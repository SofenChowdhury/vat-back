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
        Schema::create('sales_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->json('item_info')->nullable();
            $table->double('ait')->default(0)->nullable();
            $table->double('price', 12, 2);
            $table->integer('qty');
            $table->decimal('vat_rate')->default(15)->comment('Exempted is zero');
            $table->double('vat_amount', 12, 2)->default(0);
            $table->double('total_price', 12, 2)->comment("Total Price = Price*Qty");            
            $table->foreign('sales_id')->references('id')->on('sales');
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
        Schema::dropIfExists('sales_items');
    }
};
