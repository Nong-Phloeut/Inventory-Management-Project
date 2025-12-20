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
        Schema::create('purchase_statuses', function (Blueprint $table) {
            $table->string('code')->primary(); // draft, ordered, received, completed, cancelled
            $table->string('label');          // Draft, Ordered, Received...
            $table->boolean('is_final')->default(false);
            $table->boolean('affects_stock')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_statuses');
    }
};
