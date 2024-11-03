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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();            
            $table->unsignedBigInteger('customer_id')->index();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('branch_id')->index();
            $table->unsignedBigInteger('sales_by')->index();
            $table->string('sales_no');
            $table->string('customer_code');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_address')->nullable();
            $table->string('customer_national_id')->nullable();
            $table->string('ref_name')->nullable();
            $table->string('ref_address')->nullable();
            $table->string('ref_national_id')->nullable();
            $table->string('vehicle_no')->nullable();
            $table->string('destination_address')->nullable();
            $table->tinyInteger('status')->default(1)->nullable()->comment("0 for pending 1 for delivered");
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->foreign('sales_by')->references('id')->on('admins');
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
        Schema::dropIfExists('sales');
    }
};
