<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_return_items', function (Blueprint $table) {
            $table->double('challan_item_value')->after('ait')->nullable();
            $table->double('challan_item_qty')->after('challan_item_value')->nullable();
            $table->double('challan_item_vat')->after('challan_item_value')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_return_items', function (Blueprint $table) {
            $table->dropColumn('challan_item_value');
            $table->dropColumn('challan_item_qty');
            $table->dropColumn('challan_item_vat');
        });
    }
};
