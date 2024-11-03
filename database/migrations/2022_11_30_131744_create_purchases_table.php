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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();            
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('vendor_id')->index();
            $table->unsignedBigInteger('admin_id')->index();
            $table->string('purchase_no');
            $table->enum('custom_house', ['Dhaka', 'Chittagong', 'Benapole'])->nullable();
            $table->enum('type', ['Local', 'Import']);
            $table->string('challan_no');
            $table->date('challan_date')->nullable();
            $table->tinyInteger('status')->default(1)->nullable();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('vendor_id')->references('id')->on('vendors');
            $table->foreign('admin_id')->references('id')->on('admins');
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
        Schema::dropIfExists('purchases');
    }
};
