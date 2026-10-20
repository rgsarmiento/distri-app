<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('orders', 'route_id')) {
                $table->unsignedBigInteger('route_id')->nullable()->after('customer_id');
            }
            if (!Schema::hasColumn('orders', 'payment_term')) {
                $table->string('payment_term', 20)->default('CONTADO')->after('route_id');
            }
        });

        // Modificar columna status a VARCHAR(30) para soportar todo el flujo:
        // 'CRUDO', 'SEPARADO', 'REVISADO', 'DEPURADO', 'FACTURADO', 'ANULADO'
        DB::statement("ALTER TABLE orders MODIFY COLUMN status VARCHAR(30) NOT NULL DEFAULT 'CRUDO'");

        // Backfill de company_id para órdenes existentes desde la tabla users
        DB::statement("
            UPDATE orders o
            JOIN users u ON o.user_id = u.id
            SET o.company_id = u.company_id
            WHERE o.company_id IS NULL
        ");

        // Homogeneizar estados anteriores
        DB::statement("UPDATE orders SET status = 'CRUDO' WHERE status = 'pendiente'");
        DB::statement("UPDATE orders SET status = 'FACTURADO' WHERE status = 'facturado'");

        // Añadir foreign key para company_id e índices compuestos de alto rendimiento
        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');

            $table->index(['company_id', 'status'], 'idx_orders_company_status');
            $table->index(['company_id', 'created_at'], 'idx_orders_company_created_at');
            $table->index(['company_id', 'user_id'], 'idx_orders_company_user');
            $table->index(['company_id', 'route_id'], 'idx_orders_company_route');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropIndex('idx_orders_company_status');
            $table->dropIndex('idx_orders_company_created_at');
            $table->dropIndex('idx_orders_company_user');
            $table->dropIndex('idx_orders_company_route');

            $table->dropColumn(['company_id', 'route_id', 'payment_term']);
        });

        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pendiente','facturado') NOT NULL DEFAULT 'pendiente'");
    }
};
