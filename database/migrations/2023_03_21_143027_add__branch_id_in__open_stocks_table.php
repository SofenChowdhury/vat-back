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
        Schema::table('open_stocks', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->after('company_id')->default(2)->nullable()->index();
            $table->foreign('branch_id')->references('id')->on('branches');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('open_stocks', function (Blueprint $table) {
            $table->dropColumn('branch_id');
        });
    }
};
