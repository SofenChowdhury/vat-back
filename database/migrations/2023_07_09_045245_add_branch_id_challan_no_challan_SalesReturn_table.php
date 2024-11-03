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
        Schema::table('sales_returns', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->after('company_id')->nullable();
            $table->string('challan_no')->after('sl_no')->nullable();
            $table->date('challan_date')->after('challan_no')->nullable();
            $table->string('reference_no')->after('challan_date')->nullable();
            $table->foreign('branch_id')->references('id')->on('branches');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_returns', function (Blueprint $table) {
            $table->dropColumn('branch_id');
            $table->dropColumn('challan_no');
            $table->dropColumn('reference_no');
            $table->dropColumn('challan_date');
        });
    }
};
