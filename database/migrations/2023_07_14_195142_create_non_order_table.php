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
        Schema::create('non_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->string('branch_name');
            $table->string('branch_code');
            $table->string('product_title');
            $table->string('order_number');
            $table->string('sku');
            $table->double('qty', 8, 2)->nullable();
            $table->float('vat_amount', 8, 2)->nullable();
            $table->float('vat_rate', 8, 2)->nullable();
            $table->double('price', 12, 2)->nullable();
            $table->date('order_date')->nullable();
            $table->foreign('branch_id')->references('id')->on('branches');
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
        Schema::dropIfExists('non_orders');
    }
};
