<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Agregar campo de días de alerta a la tabla companies
        Schema::table('companies', function (Blueprint $table) {
            $table->unsignedInteger('alert_days')->default(30)->after('municipality')
                ->comment('Días sin pedido para generar alerta de cliente inactivo');
        });

        // Insertar el rol Supervisor si no existe
        if (!DB::table('roles')->where('name', 'Supervisor')->exists()) {
            DB::table('roles')->insert([
                'name' => 'Supervisor',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('alert_days');
        });

        DB::table('roles')->where('name', 'Supervisor')->delete();
    }
};
