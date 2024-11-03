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
        Schema::create('bom_value_additions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bom_id')->index();
            $table->unsignedBigInteger('value_addition_id');
            $table->double('amount', 8, 2)->nullable();
            $table->foreign('bom_id')->references('id')->on('boms');
            $table->foreign('value_addition_id')->references('id')->on('value_additions');
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
        Schema::dropIfExists('bom_value_additions');
    }
};
