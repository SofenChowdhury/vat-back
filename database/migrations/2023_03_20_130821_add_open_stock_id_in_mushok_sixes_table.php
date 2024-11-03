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
            $table->unsignedBigInteger('open_stock_id')->after('sales_return_id')->nullable()->index();
            $table->foreign('open_stock_id')->references('id')->on('open_stocks');
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
            $table->dropColumn('open_stock_id');
        });
    }
};
