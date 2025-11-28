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
        Schema::table('purchase_items', function (Blueprint $table) {
            // Change columns to store percentages instead of amounts
            $table->integer('item_discount')->default(0)->change();
            $table->integer('item_tax')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            // Revert to previous integer(10,2)
            $table->integer('item_discount')->default(0)->change();
            $table->integer('item_tax')->default(0)->change();
        });
    }
};
