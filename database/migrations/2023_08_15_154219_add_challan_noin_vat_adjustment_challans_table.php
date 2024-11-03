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
        Schema::table('vat_adjustment_challans', function (Blueprint $table) {
            $table->double('challan_no')->after('sales_id')->nullable();
            $table->double('challan_date')->after('challan_no')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vat_adjustment_challans', function (Blueprint $table) {
            $table->dropColumn('challan_no');
            $table->dropColumn('challan_date');
        });
    }
};
