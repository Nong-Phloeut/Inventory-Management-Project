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
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('purchase_status_code')
                ->nullable()
                ->after('supplier_id');

            $table->foreign('purchase_status_code')
                ->references('code')
                ->on('purchase_statuses')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->timestamp('completed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['purchase_status_code']);
            $table->dropColumn(['purchase_status_code', 'completed_at']);
        });
    }
};
