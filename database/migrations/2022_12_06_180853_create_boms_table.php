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
        Schema::create('boms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->index();
            $table->unsignedBigInteger('created_by')->index();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('bom_number');
            $table->date('start_date')->nullable();;
            $table->date('end_date')->nullable();
            $table->tinyInteger('status')->default(1)->nullable()->comment("0 for inactive 1 for active");
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('created_by')->references('id')->on('admins');
            $table->foreign('company_id')->references('id')->on('companies');
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
        Schema::dropIfExists('boms');
    }
};
