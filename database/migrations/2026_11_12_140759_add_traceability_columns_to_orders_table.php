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
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'separator_id')) {
                $table->unsignedBigInteger('separator_id')->nullable()->after('user_id');
                $table->foreign('separator_id')->references('id')->on('users')->onDelete('set null');
                $table->index(['company_id', 'separator_id'], 'idx_orders_company_separator');
            }

            if (!Schema::hasColumn('orders', 'reviewer_id')) {
                $table->unsignedBigInteger('reviewer_id')->nullable()->after('separator_id');
                $table->foreign('reviewer_id')->references('id')->on('users')->onDelete('set null');
                $table->index(['company_id', 'reviewer_id'], 'idx_orders_company_reviewer');
            }

            if (!Schema::hasColumn('orders', 'debugger_id')) {
                $table->unsignedBigInteger('debugger_id')->nullable()->after('reviewer_id');
                $table->foreign('debugger_id')->references('id')->on('users')->onDelete('set null');
                $table->index(['company_id', 'debugger_id'], 'idx_orders_company_debugger');
            }

            if (!Schema::hasColumn('orders', 'invoiced_at')) {
                $table->timestamp('invoiced_at')->nullable()->after('status');
                $table->index(['company_id', 'invoiced_at'], 'idx_orders_company_invoiced');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'separator_id')) {
                $table->dropForeign(['separator_id']);
                $table->dropIndex('idx_orders_company_separator');
                $table->dropColumn('separator_id');
            }

            if (Schema::hasColumn('orders', 'reviewer_id')) {
                $table->dropForeign(['reviewer_id']);
                $table->dropIndex('idx_orders_company_reviewer');
                $table->dropColumn('reviewer_id');
            }

            if (Schema::hasColumn('orders', 'debugger_id')) {
                $table->dropForeign(['debugger_id']);
                $table->dropIndex('idx_orders_company_debugger');
                $table->dropColumn('debugger_id');
            }

            if (Schema::hasColumn('orders', 'invoiced_at')) {
                $table->dropIndex('idx_orders_company_invoiced');
                $table->dropColumn('invoiced_at');
            }
        });
    }
};
