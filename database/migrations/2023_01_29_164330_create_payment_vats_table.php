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
        Schema::create('vat_payments', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('type');
            $table->string('treasury_challan_no')->nullable();
            $table->string('bank')->nullable();
            $table->string('branch')->nullable();
            $table->string('account_code')->nullable();
            $table->double('amount', 12,2)->nullable();            
            $table->date('payment_date')->nullable();
            $table->date('ledger_month')->nullable();
            $table->unsignedBigInteger('created_by')->index();
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
        Schema::dropIfExists('vat_payments');
    }
};
