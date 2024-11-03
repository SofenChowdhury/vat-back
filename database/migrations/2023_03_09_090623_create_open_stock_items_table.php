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
        Schema::create('open_stock_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('open_stock_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->json('item_info')->nullable();
            $table->double('price', 12, 2);
            $table->double('qty', 10,2);        
            $table->foreign('open_stock_id')->references('id')->on('open_stocks');
            $table->foreign('product_id')->references('id')->on('products');
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
        Schema::dropIfExists('open_stock_items');
    }
};
