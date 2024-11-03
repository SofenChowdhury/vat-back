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
        Schema::create('transfer_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transfer_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->double('price', 12, 2)->nullable();
            $table->double('qty', 12, 2)->nullable();
            $table->double('vat_percentage', 12, 2)->nullable();
            $table->double('vat_amount', 12, 2)->nullable();
            $table->string('note')->nullable();
            $table->foreign('transfer_id')->references('id')->on('transfers');
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
        Schema::dropIfExists('transfer_items');
    }
};
