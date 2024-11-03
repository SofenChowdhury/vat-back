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
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_id')->index();
            $table->unsignedBigInteger('created_by')->index();
            $table->string('return_no');
            $table->text('return_reason')->nullable();
            $table->tinyInteger('status')->default(1)->nullable();
            $table->foreign('sales_id')->references('id')->on('sales');
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
        Schema::dropIfExists('sales_returns');
    }
    
    
};
