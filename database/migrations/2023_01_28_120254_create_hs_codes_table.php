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
        Schema::create('hs_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('code_dot')->nullable();
            $table->string('description')->nullable();
            $table->double('cd', 12, 2)->nullable()->default(0.00);
            $table->double('sd', 12, 2)->nullable()->default(0.00);
            $table->double('vat', 12, 2)->nullable()->default(0.00);
            $table->double('ait', 12, 2)->nullable()->default(0.00);
            $table->double('rd', 12, 2)->nullable()->default(0.00);
            $table->double('at', 12, 2)->nullable()->default(0.00);
            $table->double('total', 12, 2)->nullable()->default(0.00);
            $table->string('note')->nullable();
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
        Schema::dropIfExists('hs_codes');
    }
};
