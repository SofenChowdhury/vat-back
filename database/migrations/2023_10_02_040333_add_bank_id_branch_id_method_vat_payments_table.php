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
            $table->string('bank_id')->after('remarks')->nullable();
            $table->string('branch_id')->after('bank')->nullable();
            $table->string('method')->after('type')->nullable();
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
            $table->dropColumn('bank_id');
            $table->dropColumn('branch_id');
            $table->dropColumn('method');
        });
    }
};
