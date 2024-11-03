<?php

use Illuminate\Database\Migrations\Migration;
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
        Schema::table('products', function($table) {
            $table->unsignedBigInteger('hs_code_id')->nullable()->after('category_id');
            $table->foreign('hs_code_id')->references('id')->on('hs_codes')->onDelete('cascade');
        });
    }
    

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function($table) {
            $table->dropColumn('hs_code_id');
        });
    }
};
