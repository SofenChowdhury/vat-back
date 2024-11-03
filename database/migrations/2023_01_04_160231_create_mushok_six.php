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
        Schema::create('mushok_sixes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('purchase_id')->nullable()->comment("Purchase and Debit Note");
            $table->unsignedBigInteger('sales_id')->nullable()->comment("Six Two");
            $table->unsignedBigInteger('finished_id')->nullable()->comment("For Six one");
            $table->unsignedBigInteger('purchase_return_id')->nullable()->comment("For Six one");
            $table->unsignedBigInteger('sales_return_id')->nullable()->comment("For Six one");
            $table->enum('type',['debit', 'credit']);
            $table->enum('mushok', ['six_one', 'six_two', 'six_two_one','six_three','six_six','six_seven','six_eight'])->nullable();
            $table->integer('qty');
            $table->integer('sales_return_qty')->default(0);
            $table->integer('purchase_return_qty')->default(0);
            $table->double('price', 12, 2)->default(0);
            $table->double('average_price', 12, 2)->nullable()->default(0);
            $table->double('vat_rate', 12, 2)->default(0);
            $table->double('vat_amount', 12, 2)->default(0);
            $table->double('cd_amount', 12, 2)->default(0);
            $table->double('rd_amount', 12, 2)->default(0);
            $table->double('sd_amount', 12, 2)->default(0);
            $table->double('ait_amount', 12, 2)->default(0);
            $table->double('others_amount', 12, 2)->default(0);
            $table->double('opening_qty', 12, 2)->default(0);
            $table->double('closing_qty', 12, 2)->default(0);
            $table->integer('created_by')->nullable();
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('purchase_id')->references('id')->on('purchases');
            $table->foreign('sales_id')->references('id')->on('sales');
            $table->foreign('finished_id')->references('id')->on('finished_goods');
            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('purchase_return_id')->references('id')->on('companies');
            $table->foreign('sales_return_id')->references('id')->on('companies');
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
        Schema::dropIfExists('mushok_sixes');
    }
};
