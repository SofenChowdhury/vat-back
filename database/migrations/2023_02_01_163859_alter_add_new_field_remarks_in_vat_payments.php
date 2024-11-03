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
        Schema::table('vat_payments', function (Blueprint $table) {
            $table->string('remarks')->nullable()->after('treasury_challan_no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vat_payments', function (Blueprint $table) {
            $table->dropColumn('remarks');
        });
    }
};
