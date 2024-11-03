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
        Schema::create('purchase_return_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_return_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->json('item_info')->nullable();
            $table->double('av')->default(0)->nullable();
            $table->double('cd')->default(0)->nullable();
            $table->double('rd')->default(0)->nullable();
            $table->double('sd')->default(0)->nullable();
            $table->double('at')->default(0)->nullable();
            $table->double('ait')->default(0)->nullable();
            $table->double('price', 12, 2);
            $table->integer('qty');
            $table->decimal('vat_rate')->default(15)->comment('Exempted is zero');
            $table->double('vat_amount', 12, 2)->default(0);           
            $table->foreign('purchase_return_id')->references('id')->on('purchase_returns');
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
        Schema::dropIfExists('purchase_return_items');
    }
};
