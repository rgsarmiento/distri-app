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
        Schema::create('order_item_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('user_id')->nullable(); // Auditor / operario que ejecuta el cambio
            $table->unsignedBigInteger('original_product_id');
            $table->decimal('original_quantity', 12, 2);
            $table->unsignedBigInteger('new_product_id')->nullable(); // Null si fue eliminación
            $table->decimal('new_quantity', 12, 2)->default(0);
            $table->enum('action_type', ['SUBSTITUTE', 'REMOVE', 'ADJUST_QTY'])->default('SUBSTITUTE');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('original_product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade');

            $table->foreign('new_product_id')
                ->references('id')
                ->on('products')
                ->onDelete('set null');

            $table->index(['order_id', 'created_at'], 'idx_oia_order_created');
            $table->index(['original_product_id'], 'idx_oia_original_product');
            $table->index(['new_product_id'], 'idx_oia_new_product');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_item_audits');
    }
};
