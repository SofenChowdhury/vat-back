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
        Schema::table('mushok_sixes', function (Blueprint $table) {
            $table->double('vat_rebetable_amount', 12, 2)->nullable()->default(0)->after('vds_receive_amount');
            $table->double('at_amount', 12, 2)->nullable()->default(0)->after('ait_amount');
            $table->double('tti', 12, 2)->nullable()->default(0)->after('at_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mushok_sixes', function (Blueprint $table) {
            $table->dropColumn('vat_rebetable_amount');
            $table->dropColumn('at_amount');
            $table->dropColumn('tti');
        });
    }
};
