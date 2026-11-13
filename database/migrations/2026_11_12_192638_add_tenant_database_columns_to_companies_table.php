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
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'db_database')) {
                $table->string('db_database')->nullable()->after('alert_days');
                $table->string('db_host')->nullable()->after('db_database');
                $table->string('db_port', 10)->nullable()->after('db_host');
                $table->string('db_username')->nullable()->after('db_port');
                $table->string('db_password')->nullable()->after('db_username');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $columns = ['db_database', 'db_host', 'db_port', 'db_username', 'db_password'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('companies', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
