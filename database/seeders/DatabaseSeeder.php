<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * Este seeder solo se encarga de la configuración esencial del sistema.
     * NO crea registros de empresas, productos ni clientes por defecto
     * para evitar contaminación en el entorno de producción.
     */
    public function run(): void
    {
        // 1. Configuración de Roles (Admin, Distribuidor, Supervisor)
        // Este paso es esencial para el funcionamiento del middleware y permisos.
        $this->call(RoleSeeder.class);

        // 2. Datos de Demostración (Opcional)
        // Solo se ejecutan en entorno local si el desarrollador lo desea explícitamente.
        /*
        if (app()->environment('local')) {
            $this->call(DemoDataSeeder::class);
        }
        */
    }
}
