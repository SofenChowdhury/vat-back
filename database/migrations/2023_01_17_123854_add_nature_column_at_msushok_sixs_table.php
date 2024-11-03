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
        Schema::table('mushok_sixes', function($table) {
            $table->string('nature')->after('mushok')->nullable()->comment('For Local/Imported/Wholesale/Rebetable');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mushok_sixes', function($table) {
            $table->dropColumn('nature');
        });
    }
};
