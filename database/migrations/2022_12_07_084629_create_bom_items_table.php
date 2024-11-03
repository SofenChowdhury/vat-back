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
        Schema::create('bom_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bom_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->json('item_info')->nullable();
            $table->string('unit')->nullable()->default("PCS")->comment('PCS, KG, CFT, SET, GM, MTR, UNIT');
            $table->decimal('actual_qty')->nullable()->default(1);
            $table->decimal('qty_with_wastage')->nullable()->default(0)->comment('Actual qty and wastage');
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
        Schema::dropIfExists('bom_items');
    }
};
