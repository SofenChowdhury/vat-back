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
        Schema::table('non_orders', function (Blueprint $table) {
            $table->json('raw_data')->after('product_id')->nullable();
            $table->tinyInteger('type')->after('order_date')->comment("1 for order 2 for transfer")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('non_orders', function (Blueprint $table) {
            $table->dropColumn('raw_data');
            $table->dropColumn('type');
        });
    }
};
