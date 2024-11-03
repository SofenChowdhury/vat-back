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
        Schema::table('mushok_sixes', function (Blueprint $table) {
            $table->tinyInteger('printed')->after('closing_qty')->nullable()->default(0)->comment('0=Not print, 1= Printed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mushok_sixes', function (Blueprint $table) {
            $table->dropColumn('printed');
        });
    }
};
