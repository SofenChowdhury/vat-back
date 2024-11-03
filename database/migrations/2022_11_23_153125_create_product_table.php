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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->index();
            $table->string('title');
            $table->string('sku')->index();
            $table->string('slug')->nullable();
            $table->string('model')->nullable();           
            $table->text('details')->nullable();
            $table->enum('type', ['1', '2', '3', '4'])->default(1)->comment('1 for finish goods, 2 for Raw Materials, 3 for Accessories and 4 for Services');
            $table->decimal('vat_rate')->default(15)->comment('Exempted is zero');
            $table->double('price', 12, 2);
            $table->string('photo')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->foreign('category_id')->references('id')->on('categories');
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
        Schema::dropIfExists('products');
    }
};
