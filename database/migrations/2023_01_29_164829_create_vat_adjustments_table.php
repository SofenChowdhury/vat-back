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
        Schema::create('vat_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index()->nullable();
            $table->unsignedBigInteger('created_by')->index()->nullable();
            $table->enum('type', ['increasing', 'decreasing'])->comment("INCREASING for purchase decreasing for SALES");
            $table->string('bank')->nullable();
            $table->string('branch')->nullable();
            $table->string('reference_no')->nullable();
            $table->string('account_code')->nullable();
            $table->string('challan_no')->nullable();
            $table->string('note_no')->nullable();
            $table->string('certificate_no')->nullable();
            $table->date('certificate_date')->nullable();
            $table->double('amount', 12,2)->nullable();
            $table->double('vat', 12,2)->nullable();
            $table->date('deposit_date')->nullable();
            $table->date('ledger_month')->nullable();
            $table->string('remarks')->nullable();            
            $table->foreign('created_by')->references('id')->on('admins');
            $table->foreign('company_id')->references('id')->on('companies');
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
        Schema::dropIfExists('vat_adjustments');
    }
};
