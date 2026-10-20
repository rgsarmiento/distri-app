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
        Schema::table('customer_details', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_details', 'route_id')) {
                $table->unsignedBigInteger('route_id')->nullable()->after('municipality');
            }
            if (!Schema::hasColumn('customer_details', 'visit_monday')) {
                $table->boolean('visit_monday')->default(false)->after('route_id');
                $table->boolean('visit_tuesday')->default(false)->after('visit_monday');
                $table->boolean('visit_wednesday')->default(false)->after('visit_tuesday');
                $table->boolean('visit_thursday')->default(false)->after('visit_wednesday');
                $table->boolean('visit_friday')->default(false)->after('visit_thursday');
                $table->boolean('visit_saturday')->default(false)->after('visit_friday');
                $table->boolean('visit_sunday')->default(false)->after('visit_saturday');
            }
            if (!Schema::hasColumn('customer_details', 'web_access')) {
                $table->boolean('web_access')->default(false)->after('visit_sunday');
                $table->string('web_user')->nullable()->after('web_access');
                $table->string('web_password')->nullable()->after('web_user');
            }
            if (!Schema::hasColumn('customer_details', 'discount_rate')) {
                $table->decimal('discount_rate', 5, 2)->default(0)->after('web_password');
            }
        });

        // Índices compuestos para consultas multi-tenant de alto rendimiento
        Schema::table('customer_details', function (Blueprint $table) {
            $table->index(['company_id', 'identification'], 'idx_cd_company_identification');
            $table->index(['company_id', 'route_id'], 'idx_cd_company_route');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_details', function (Blueprint $table) {
            $table->dropIndex('idx_cd_company_identification');
            $table->dropIndex('idx_cd_company_route');

            $table->dropColumn([
                'route_id',
                'visit_monday',
                'visit_tuesday',
                'visit_wednesday',
                'visit_thursday',
                'visit_friday',
                'visit_saturday',
                'visit_sunday',
                'web_access',
                'web_user',
                'web_password',
                'discount_rate',
            ]);
        });
    }
};
