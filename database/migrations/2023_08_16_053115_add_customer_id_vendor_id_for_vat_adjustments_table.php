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
        Schema::table('vat_adjustments', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->after('company_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->after('customer_id')->nullable();
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('vendor_id')->references('id')->on('vendors');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vat_adjustments', function (Blueprint $table) {
            $table->dropColumn('customer_id');
            $table->dropColumn('vendor_id');
        });
    }
};
