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
        Schema::create('sales_return_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_return_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->json('item_info')->nullable();
            $table->double('sd')->default(0)->nullable();
            $table->double('ait')->default(0)->nullable();
            $table->double('price', 12, 2);
            $table->double('qty', 12,2);
            $table->double('vat_rate', 12,2)->default(15)->comment('Exempted is zero');
            $table->double('vat_amount', 12, 2)->default(0);           
            $table->foreign('sales_return_id')->references('id')->on('sales_returns');
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
        Schema::dropIfExists('sales_return_items');
    }
};
