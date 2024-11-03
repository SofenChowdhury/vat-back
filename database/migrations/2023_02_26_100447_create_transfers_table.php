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
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('branch_from_id')->index()->nullable();
            $table->unsignedBigInteger('branch_to_id')->index()->nullable();
            $table->string('transfer_no');
            $table->string('vehicle_info')->nullable()->comment('Vehicle No., Driver Name etc.');
            $table->string('note')->nullable();
            $table->enum('status', ['pending', 'received'])->default('pending');
            $table->unsignedBigInteger('created_by')->index();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('branch_from_id')->references('id')->on('branches');
            $table->foreign('branch_to_id')->references('id')->on('branches');
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
        Schema::dropIfExists('transfers');
    }
};
