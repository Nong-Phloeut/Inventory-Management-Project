<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    public function up()
    {
        Schema::table('purchase_items', function (Blueprint $table) {

            // If the column does NOT exist, create it.
            if (!Schema::hasColumn('purchase_items', 'item_discount')) {
                $table->integer('item_discount')->default(0);
            } else {
                // If exists, modify it.
                $table->integer('item_discount')->default(0)->change();
            }
        });
    }

    public function down()
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn('item_discount');
        });
    }

};
