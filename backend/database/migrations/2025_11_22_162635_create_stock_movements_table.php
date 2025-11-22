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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->enum('movement_type', [
                'purchase',
                'sale',
                'return',
                'adjustment',
                'transfer_in',
                'transfer_out',
                'loss'
            ]);

            $table->decimal('qty', 10, 2);
            $table->decimal('cost', 10, 2)->default(0);

            $table->unsignedBigInteger('related_id')->nullable(); // purchase_id, order_id, transfer_id...
            $table->text('note')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Foreign Keys
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
