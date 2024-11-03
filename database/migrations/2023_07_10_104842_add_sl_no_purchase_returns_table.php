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
        Schema::table('purchase_returns', function (Blueprint $table) {
            $table->integer('sl_no')->after('id')->nullable();
            $table->unsignedBigInteger('vendor_id')->after('purchase_id')->nullable();
            $table->unsignedBigInteger('company_id')->after('vendor_id')->nullable();
            $table->unsignedBigInteger('branch_id')->after('company_id')->nullable();
            $table->string('challan_no')->nullable();
            $table->string('challan_date')->nullable();
            $table->string('reference_no')->nullable();
            $table->foreign('vendor_id')->references('id')->on('vendors');
            $table->foreign('company_id')->references('id')->on('companies');
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
        Schema::table('purchase_returns', function (Blueprint $table) {
            $table->dropColumn('sl_no');
            $table->dropColumn('vendor_id');
            $table->dropColumn('company_id');
            $table->dropColumn('branch_id');
            $table->dropColumn('challan_no');
            $table->dropColumn('challan_date');
            $table->dropColumn('reference_no');
        });
    }
};
