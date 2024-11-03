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
        Schema::create('finished_goods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->index();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('branch_id')->index();
            $table->unsignedBigInteger('created_by')->index();
            $table->string('goods_no');
            $table->integer('qty');
            $table->date('production_date');
            $table->double('price', 12, 2);
            $table->decimal('vat_rate')->nullable()->default(15)->comment('Exempted is zero');
            $table->enum('type', ['Received', 'Canceled'])->default("Received");
            $table->string('challan_no')->nullable();
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->foreign('created_by')->references('id')->on('admins');
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
        Schema::dropIfExists('finished_goods');
    }
};
