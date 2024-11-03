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
        Schema::create('value_additions', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('serial');
            $table->string('head');            
            $table->decimal('percentage', 8, 2)->nullable();
            $table->integer('amount')->nullable();
            $table->tinyInteger('status')->default(1)->comment('zero for inactive and 1 for activated');
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
        Schema::dropIfExists('value_addition_heads');
    }
};
