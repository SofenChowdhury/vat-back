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
        Schema::create('vendors', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('logo')->nullable();
                $table->string('vendor_code')->nullable();
                $table->string('contact_person')->nullable();
                $table->string('contact_number')->nullable();
                $table->string('contact_email')->nullable();
                $table->string('contact_address')->nullable();
                $table->string('vendor_tin')->nullable();
                $table->string('vendor_bin')->nullable();
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
        Schema::dropIfExists('vendors');
    }
};
