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
        Schema::create('vat_adjustment_challans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vat_adjustment_id')->index();
            $table->unsignedBigInteger('purchase_id')->index()->nullable();
            $table->unsignedBigInteger('sales_id')->index()->nullable();
            $table->double('value', 12, 2)->nullable();
            $table->double('amount', 12, 2)->nullable();
            $table->foreign('purchase_id')->references('id')->on('purchases');
            $table->foreign('sales_id')->references('id')->on('sales');
            $table->foreign('vat_adjustment_id')->references('id')->on('vat_adjustments');
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
        Schema::dropIfExists('vat_adjustment_challans');
    }
};
