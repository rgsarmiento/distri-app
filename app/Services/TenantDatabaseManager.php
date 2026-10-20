<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TenantDatabaseManager
{
    /**
     * Nombre de la conexión dinámica de tenant
     */
    public const TENANT_CONNECTION = 'tenant';

    /**
     * Conecta dinámicamente a la base de datos propia de una empresa.
     * Si la empresa tiene configurada base de datos propia (en tabla companies o .env),
     * conmuta la conexión 'tenant'. Si no, usa la conexión compartida por defecto.
     *
     * @param Company|int $company
     * @return string Nombre de la conexión activa ('tenant' o 'mysql')
     */
    public function switchToTenant($company): string
    {
        if (is_numeric($company)) {
            $company = Company::findOrFail($company);
        }

        // Si la empresa tiene credenciales dedicadas
        if (!empty($company->db_database)) {
            $defaultConfig = Config::get('database.connections.mysql');

            $tenantConfig = array_merge($defaultConfig, [
                'host'     => $company->db_host ?: $defaultConfig['host'],
                'port'     => $company->db_port ?: $defaultConfig['port'],
                'database' => $company->db_database,
                'username' => $company->db_username ?: $defaultConfig['username'],
                'password' => $company->db_password ?: $defaultConfig['password'],
            ]);

            Config::set('database.connections.' . self::TENANT_CONNECTION, $tenantConfig);
            DB::purge(self::TENANT_CONNECTION);
            DB::reconnect(self::TENANT_CONNECTION);

            return self::TENANT_CONNECTION;
        }

        // Conexión estándar multi-tenant por columna company_id
        return Config::get('database.default');
    }

    /**
     * Restablece la conexión a la base de datos principal
     */
    public function resetConnection(): void
    {
        DB::purge(self::TENANT_CONNECTION);
        DB::setDefaultConnection(Config::get('database.default'));
    }
}
