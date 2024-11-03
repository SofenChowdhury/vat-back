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
        Schema::create('return_summaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('return_submitted_by')->index();
            $table->double('total_sales_amount', 12, 2)->default(0)->nullable();
            $table->double('total_sales_vat', 12, 2)->default(0)->nullable();
            $table->double('total_purchase_amount', 12, 2)->default(0)->nullable();
            $table->double('total_purchase_vat', 12, 2)->default(0)->nullable();
            $table->double('total_purchase_rebatable_vat', 12, 2)->default(0)->nullable();
            $table->double('total_increasing_amount', 12, 2)->default(0)->nullable();
            $table->double('total_decreasing_amount', 12, 2)->default(0)->nullable();
            $table->double('opening_balance', 12, 2)->default(0)->nullable();
            $table->double('deposited_vat', 12, 2)->default(0)->nullable();
            $table->double('closing_balance', 12, 2)->default(0)->nullable();
            $table->string('remarks')->nullable();
            $table->date('return_submitted_at')->nullable();
            $table->date('ledger_month');
            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('return_submitted_by')->references('id')->on('admins');
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
        Schema::dropIfExists('return_summaries');
    }
};
