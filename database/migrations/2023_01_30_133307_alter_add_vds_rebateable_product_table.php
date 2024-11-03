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
        Schema::table('products', function($table) {
            $table->double('vat_rebatable_percentage', 12,2)->default(0)->after('price');
            $table->double('vds_percentage', 12,2)->default(0)->after('vat_rebatable_percentage');
            $table->string('unit_type')->nullable()->after('vds_percentage');
            $table->string('origin')->nullable()->after('unit_type');
            
        });
    }
    

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function($table) {
            $table->dropColumn('vat_rebatable_percentage');
            $table->dropColumn('vds_percentage');
            $table->dropColumn('unit_type');
            $table->dropColumn('origin');
        });
    }
};
