<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\TenantDatabaseManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TenantIsolateCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tenant:isolate 
                            {company_id : ID de la empresa a aislar en base de datos propia}
                            {--database= : Nombre personalizado de la base de datos (por defecto: distri_empresa_{id})}
                            {--force : Omitir confirmación interactiva}';

    /**
     * The console command description.
     */
    protected $description = 'Aísla una empresa existente de la base de datos compartida a su propia base de datos dedicada, copiando todos sus datos históricos';

    public function handle(TenantDatabaseManager $tenantManager): int
    {
        $companyId = (int) $this->argument('company_id');
        $centralConn = Config::get('database.default', 'mysql');

        $company = Company::on($centralConn)->find($companyId);
        if (!$company) {
            $this->error("Empresa con ID {$companyId} no encontrada en la base de datos central.");
            return Command::FAILURE;
        }

        $dbName = $this->option('database') ?: "distri_empresa_{$company->id}";
        // Sanitizar nombre de BD
        $dbName = preg_replace('/[^a-zA-Z0-9_]/', '', $dbName);

        $this->info("══════════════════════════════════════════════════════════════");
        $this->info("📦 AISLAMIENTO DE EMPRESA A BASE DE DATOS DEDICADA");
        $this->info("══════════════════════════════════════════════════════════════");
        $this->line("Empresa:        #{$company->id} - {$company->name} (NIT: {$company->nit})");
        $this->line("Nueva Base BD:  {$dbName}");
        $this->line("Estado actual:  " . ($company->hasDedicatedDatabase() ? "Ya dedicada ({$company->db_database})" : "Base compartida central"));

        if (!$this->option('force') && !$this->confirm("¿Deseas proceder con la creación y migración de datos para {$company->name}?", true)) {
            $this->comment("Operación cancelada por el usuario.");
            return Command::SUCCESS;
        }

        try {
            // ── 1. Crear base de datos en MySQL ────────────────────────
            $this->comment("\n1. Creando base de datos física [{$dbName}]...");
            $tenantManager->createDatabaseIfNotExists($dbName);
            $this->info("✔ Base de datos [{$dbName}] creada/verificada.");

            // ── 2. Configurar temporalmente conexión tenant ────────────
            $defaultConfig = Config::get('database.connections.mysql');
            $tenantConfig = array_merge($defaultConfig, [
                'database' => $dbName,
            ]);
            Config::set('database.connections.' . TenantDatabaseManager::TENANT_CONNECTION, $tenantConfig);
            DB::purge(TenantDatabaseManager::TENANT_CONNECTION);
            DB::reconnect(TenantDatabaseManager::TENANT_CONNECTION);

            // ── 3. Ejecutar migraciones en la nueva base de datos ──────
            $this->comment("2. Ejecutando estructura y migraciones en [{$dbName}]...");
            $exitCode = Artisan::call('migrate', [
                '--database' => TenantDatabaseManager::TENANT_CONNECTION,
                '--force'    => true,
            ], $this->output);

            if ($exitCode !== 0) {
                $this->error("❌ Error ejecutando migraciones en la nueva base de datos. Operación abortada.");
                return Command::FAILURE;
            }
            $this->info("✔ Estructura de tablas y columnas migrada con éxito.");

            // ── 4. Copiar datos de la empresa de la BD central a la nueva ─
            $this->comment("3. Copiando datos históricos de la empresa #{$companyId} a la base de datos aislada...");

            $tenantDb = DB::connection(TenantDatabaseManager::TENANT_CONNECTION);
            $centralDb = DB::connection($centralConn);

            // Desactivar temporalmente foreign key checks en el destino durante la importación
            $tenantDb->statement('SET FOREIGN_KEY_CHECKS=0;');

            $counts = [];

            // A. Roles
            $roles = $centralDb->table('roles')->get();
            if ($roles->isNotEmpty()) {
                $tenantDb->table('roles')->delete();
                $tenantDb->table('roles')->insert(json_decode(json_encode($roles), true));
            }
            $counts['roles'] = $roles->count();

            // B. Company
            $compRow = $centralDb->table('companies')->where('id', $companyId)->first();
            $compData = (array) $compRow;
            $compData['db_database'] = $dbName;
            $tenantDb->table('companies')->where('id', $companyId)->delete();
            $tenantDb->table('companies')->insert($compData);
            $counts['companies'] = 1;

            // C. Users
            $users = $centralDb->table('users')->where('company_id', $companyId)->get();
            if ($users->isNotEmpty()) {
                $tenantDb->table('users')->insert(json_decode(json_encode($users), true));
            }
            $counts['users'] = $users->count();

            // D. Products
            $products = $centralDb->table('products')->where('company_id', $companyId)->get();
            if ($products->isNotEmpty()) {
                $tenantDb->table('products')->insert(json_decode(json_encode($products), true));
            }
            $counts['products'] = $products->count();

            // E. Customers
            $customers = $centralDb->table('customer_details')->where('company_id', $companyId)->get();
            if ($customers->isNotEmpty()) {
                $tenantDb->table('customer_details')->insert(json_decode(json_encode($customers), true));
            }
            $counts['customers'] = $customers->count();

            // F. Customer User (pivots de asignación de vendedor)
            if ($customers->isNotEmpty()) {
                $customerIds = $customers->pluck('id')->toArray();
                $customerUsers = $centralDb->table('customer_user')->whereIn('customer_detail_id', $customerIds)->get();
                if ($customerUsers->isNotEmpty()) {
                    $tenantDb->table('customer_user')->insert(json_decode(json_encode($customerUsers), true));
                }
                $counts['customer_user'] = $customerUsers->count();
            }

            // G. Orders
            $orders = $centralDb->table('orders')->where('company_id', $companyId)->get();
            if ($orders->isNotEmpty()) {
                $orderIds = $orders->pluck('id')->toArray();
                $tenantDb->table('orders')->insert(json_decode(json_encode($orders), true));

                // H. Order Products
                $orderProducts = $centralDb->table('order_products')->whereIn('order_id', $orderIds)->get();
                if ($orderProducts->isNotEmpty()) {
                    // Insertar en lotes de 200 para alto volumen
                    foreach ($orderProducts->chunk(200) as $chunk) {
                        $tenantDb->table('order_products')->insert(json_decode(json_encode($chunk), true));
                    }
                }
                $counts['order_products'] = $orderProducts->count();

                // I. Order Item Audits
                $audits = $centralDb->table('order_item_audits')->whereIn('order_id', $orderIds)->get();
                if ($audits->isNotEmpty()) {
                    $tenantDb->table('order_item_audits')->insert(json_decode(json_encode($audits), true));
                }
                $counts['order_item_audits'] = $audits->count();
            } else {
                $counts['order_products'] = 0;
                $counts['order_item_audits'] = 0;
            }
            $counts['orders'] = $orders->count();

            // Reactivar foreign key checks
            $tenantDb->statement('SET FOREIGN_KEY_CHECKS=1;');

            // ── 5. Actualizar registro en base de datos central ─────────
            $company->update(['db_database' => $dbName]);

            $this->newLine();
            $this->info("✔ Todos los datos fueron transferidos a [{$dbName}] con éxito.");
            $this->table(['Tabla', 'Registros Aislados'], [
                ['roles', $counts['roles']],
                ['companies', $counts['companies']],
                ['users', $counts['users']],
                ['products', $counts['products']],
                ['customer_details', $counts['customers']],
                ['customer_user', $counts['customer_user'] ?? 0],
                ['orders', $counts['orders']],
                ['order_products', $counts['order_products']],
                ['order_item_audits', $counts['order_item_audits']],
            ]);

            $this->info("🎉 ¡Empresa #{$companyId} ({$company->name}) aislada satisfactoriamente!");
            $this->comment("A partir de ahora, todas sus peticiones web y de API se conectan a [{$dbName}].");

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $this->error("❌ Error durante el aislamiento: " . $e->getMessage());
            return Command::FAILURE;
        } finally {
            $tenantManager->resetConnection();
        }
    }
}
