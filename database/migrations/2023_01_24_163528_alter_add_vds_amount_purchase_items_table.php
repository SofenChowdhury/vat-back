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
        Schema::table('purchase_items', function($table) {
            $table->double('vds_receive_amount', 12, 2)->after('vat_amount')->nullable()->default(0)->comment('For receiving VAT on invoice');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_items', function($table) {
            $table->dropColumn('vds_receive_amount');
        });
    }
};
