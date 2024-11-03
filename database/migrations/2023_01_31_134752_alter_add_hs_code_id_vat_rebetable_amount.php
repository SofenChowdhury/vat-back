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
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->unsignedBigInteger('hs_code_id')->nullable()->after('product_id');
            $table->double('vat_rebetable_amount', 12, 2)->nullable()->default(0)->after('vat_amount');
            $table->double('tti', 12, 2)->nullable()->default(0)->after('ait');
            $table->foreign('hs_code_id')->references('id')->on('hs_codes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn('hs_code_id');
            $table->dropColumn('vat_rebetable_amount');
            $table->dropColumn('tti');
        });
    }
};
