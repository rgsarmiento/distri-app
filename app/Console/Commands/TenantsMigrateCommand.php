<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\TenantDatabaseManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class TenantsMigrateCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tenants:migrate 
                            {--company= : ID específico de una empresa a migrar (opcional)}
                            {--fresh : Ejecuta migrate:fresh en los tenants (¡precaución! borra datos de prueba)}
                            {--seed : Ejecuta los seeders en los tenants tras migrar}';

    /**
     * The console command description.
     */
    protected $description = 'Ejecuta las migraciones en la base de datos central y en todas las bases de datos dedicadas de los tenants';

    public function handle(TenantDatabaseManager $tenantManager): int
    {
        $this->info('──────────────────────────────────────────────────────────');
        $this->info('🚀 INICIANDO MIGRACIONES MULTI-TENANT');
        $this->info('──────────────────────────────────────────────────────────');

        $isFresh = $this->option('fresh');
        $shouldSeed = $this->option('seed');
        $commandName = $isFresh ? 'migrate:fresh' : 'migrate';

        // ── 1. Migrar Base de Datos Central Compartida ───────────────
        $centralDb = Config::get('database.connections.mysql.database');
        $this->comment("1. Migrando base de datos central compartida [{$centralDb}]...");

        $exitCode = Artisan::call($commandName, [
            '--database' => Config::get('database.default', 'mysql'),
            '--force'    => true,
        ], $this->output);

        if ($exitCode !== 0) {
            $this->error("❌ Error migrando la base central. Abortando.");
            return $exitCode;
        }

        $this->info("✔ Base central [{$centralDb}] actualizada correctamente.\n");

        // ── 2. Obtener Tenants con Base de Datos Dedicada ─────────────
        $companyOption = $this->option('company');
        $query = $tenantManager->getDedicatedCompanies();

        if ($companyOption) {
            $query = $query->where('id', (int) $companyOption);
        }

        if ($query->isEmpty()) {
            $this->comment('ℹ No hay empresas con base de datos dedicada configurada (todas operan en la base compartida).');
            return Command::SUCCESS;
        }

        $this->comment("2. Migrando bases de datos dedicadas ({$query->count()} empresas)...");

        $results = [];

        foreach ($query as $company) {
            $dbName = $company->db_database;
            $this->line(" → Procesando Empresa #{$company->id} - {$company->name} [DB: {$dbName}]");

            try {
                // Asegurar que la base de datos exista
                $tenantManager->createDatabaseIfNotExists($dbName);

                // Conectar al tenant
                $tenantManager->switchToTenant($company);

                // Ejecutar migraciones en la conexión tenant
                $params = [
                    '--database' => TenantDatabaseManager::TENANT_CONNECTION,
                    '--force'    => true,
                ];

                $tenantExit = Artisan::call($commandName, $params, $this->output);

                if ($shouldSeed) {
                    Artisan::call('db:seed', [
                        '--database' => TenantDatabaseManager::TENANT_CONNECTION,
                        '--force'    => true,
                    ]);
                }

                $status = ($tenantExit === 0) ? '✔ OK' : '❌ Error';
                $results[] = [$company->id, $company->name, $dbName, $status];

            } catch (\Throwable $e) {
                $this->error("   Error en {$dbName}: " . $e->getMessage());
                $results[] = [$company->id, $company->name, $dbName, '❌ Excepción: ' . $e->getMessage()];
            } finally {
                $tenantManager->resetConnection();
            }
        }

        $this->newLine();
        $this->table(['ID', 'Empresa', 'Base de Datos', 'Estado'], $results);
        $this->info('✔ Proceso de migraciones multi-tenant finalizado.');

        return Command::SUCCESS;
    }
}
