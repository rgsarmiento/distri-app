<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Índice en status para agilizar la búsqueda de pedidos pendientes/facturados
        Schema::table('orders', function (Blueprint $table) {
            $table->index('status');
        });

        // Índice en company_id de usuarios para agilizar el filtro por empresa
        Schema::table('users', function (Blueprint $table) {
            $table->index('company_id');
        });
        
        // Índice en identification de clientes para búsquedas rápidas
        Schema::table('customer_details', function (Blueprint $table) {
            $table->index('identification');
            $table->index('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['company_id']);
        });

        Schema::table('customer_details', function (Blueprint $table) {
            $table->dropIndex(['identification']);
            $table->dropIndex(['company_id']);
        });
    }
};
