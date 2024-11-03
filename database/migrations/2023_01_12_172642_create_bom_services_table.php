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
        Schema::create('bom_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bom_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->double('amount', 8, 2)->nullable();
            $table->foreign('bom_id')->references('id')->on('boms');
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
        Schema::dropIfExists('bom_services');
    }
};
