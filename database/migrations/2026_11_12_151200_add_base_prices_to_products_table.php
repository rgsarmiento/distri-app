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
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'base_price_1')) {
                $table->decimal('base_price_1', 10, 2)->nullable()->default(0)->after('base_price');
            }
            if (!Schema::hasColumn('products', 'base_price_2')) {
                $table->decimal('base_price_2', 10, 2)->nullable()->default(0)->after('base_price_1');
            }
            if (!Schema::hasColumn('products', 'base_price_3')) {
                $table->decimal('base_price_3', 10, 2)->nullable()->default(0)->after('base_price_2');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('products', 'base_price_1')) $cols[] = 'base_price_1';
            if (Schema::hasColumn('products', 'base_price_2')) $cols[] = 'base_price_2';
            if (Schema::hasColumn('products', 'base_price_3')) $cols[] = 'base_price_3';
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
