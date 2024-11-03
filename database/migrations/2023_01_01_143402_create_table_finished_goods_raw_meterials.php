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
        Schema::create('finished_goods_raw_materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('finished_goods_id');
            $table->unsignedBigInteger('finished_item_id');
            $table->unsignedBigInteger('raw_item_id');
            $table->integer('qty');
            $table->integer('price');
            $table->foreign('finished_item_id')->references('id')->on('products');
            $table->foreign('raw_item_id')->references('id')->on('products');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('finished_goods_raw_materials');
    }
};
