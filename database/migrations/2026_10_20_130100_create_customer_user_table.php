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
        if (!Schema::hasTable('customer_user')) {
            Schema::create('customer_user', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('company_id')->nullable();
                $table->timestamps();

                $table->foreign('customer_id')
                    ->references('id')
                    ->on('customer_details')
                    ->onDelete('cascade');

                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');

                $table->foreign('company_id')
                    ->references('id')
                    ->on('companies')
                    ->onDelete('cascade');

                $table->unique(['customer_id', 'user_id'], 'uniq_customer_user');
                $table->index(['company_id', 'user_id'], 'idx_cu_company_user');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_user');
    }
};
