<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Type\Integer;

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
            $table->unsignedBigInteger('branch_id')->nullable()->after('company_id')->comment('1 for Manufacturing 2 for Trad and 3 for both');
            $table->unsignedBigInteger('transfer_id')->nullable()->after('branch_id');
            $table->integer('branch_opening')->nullable()->default(0);
            $table->integer('branch_closing')->nullable()->default(0);
            $table->tinyInteger('is_transfer')->nullable()->default(0);
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->foreign('transfer_id')->references('id')->on('transfers');
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
            $table->dropColumn('branch_id');
            $table->dropColumn('is_transfer');
        });
    }
};
