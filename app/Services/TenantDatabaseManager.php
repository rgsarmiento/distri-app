<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TenantDatabaseManager
{
    /**
     * Nombre de la conexión dinámica de tenant
     */
    public const TENANT_CONNECTION = 'tenant';

    protected static ?Company $currentTenant = null;
    protected static ?string $originalDefaultConnection = null;

    public function __construct()
    {
        if (static::$originalDefaultConnection === null) {
            static::$originalDefaultConnection = Config::get('database.default', 'mysql');
        }
    }

    /**
     * Conecta dinámicamente a la base de datos propia de una empresa.
     * Si la empresa tiene configurada base de datos propia, configura y establece
     * la conexión 'tenant' como default. Si no, restablece a la conexión compartida por defecto.
     *
     * @param Company|int $company
     * @return string Nombre de la conexión activa ('tenant' o 'mysql')
     */
    public function switchToTenant($company): string
    {
        if (static::$originalDefaultConnection === null) {
            static::$originalDefaultConnection = Config::get('database.default', 'mysql');
        }

        if (is_numeric($company)) {
            $company = Company::find($company);
        }

        if (!$company) {
            $this->resetConnection();
            return static::$originalDefaultConnection ?: 'mysql';
        }

        // Si la empresa tiene base de datos dedicada
        if ($company->hasDedicatedDatabase()) {
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
            DB::setDefaultConnection(self::TENANT_CONNECTION);

            static::$currentTenant = $company;

            return self::TENANT_CONNECTION;
        }

        // Si es una empresa en base de datos compartida
        $this->resetConnection();
        static::$currentTenant = $company;

        return static::$originalDefaultConnection ?: 'mysql';
    }

    /**
     * Restablece la conexión a la base de datos principal compartida
     */
    public function resetConnection(): void
    {
        static::$currentTenant = null;
        DB::purge(self::TENANT_CONNECTION);
        $original = static::$originalDefaultConnection ?: 'mysql';
        Config::set('database.default', $original);
        DB::setDefaultConnection($original);
    }

    /**
     * Obtiene el tenant activo actualmente (si existe)
     */
    public function getCurrentTenant(): ?Company
    {
        return static::$currentTenant;
    }

    /**
     * Comprueba si la conexión activa actual es la de un tenant aislado
     */
    public function isDedicatedActive(): bool
    {
        return DB::getDefaultConnection() === self::TENANT_CONNECTION;
    }

    /**
     * Obtiene todas las empresas que tienen base de datos dedicada configurada
     *
     * @return Collection<int, Company>
     */
    public function getDedicatedCompanies(): Collection
    {
        $original = static::$originalDefaultConnection ?: 'mysql';
        return Company::on($original)
            ->dedicated()
            ->get();
    }

    /**
     * Crea físicamente la base de datos MySQL en el servidor para una empresa si no existe
     */
    public function createDatabaseIfNotExists(string $dbName, ?string $charset = 'utf8mb4', ?string $collation = 'utf8mb4_unicode_ci'): bool
    {
        $cleanDbName = preg_replace('/[^a-zA-Z0-9_]/', '', $dbName);
        if (empty($cleanDbName)) {
            throw new \InvalidArgumentException("Nombre de base de datos inválido: {$dbName}");
        }

        $original = static::$originalDefaultConnection ?: 'mysql';
        DB::connection($original)
            ->statement("CREATE DATABASE IF NOT EXISTS `{$cleanDbName}` CHARACTER SET {$charset} COLLATE {$collation}");

        return true;
    }
}
