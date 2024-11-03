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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('submitted_by')->nullable();
            $table->string('title');
            $table->text('description');
            $table->string('file')->nullable();
            $table->string('status')->nullable();
            $table->string('module')->nullable();
            $table->string('priority')->default('normal'); // Add the priority column
            $table->timestamps();

            $table->foreign('submitted_by')->references('id')->on('admins');
        });

        Schema::create('assigned_tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('assigned_to_id');
            $table->unsignedBigInteger('assigned_by_id');
            $table->timestamp('deadline')->nullable();
            $table->timestamps();

            $table->foreign('ticket_id')->references('id')->on('tickets');
            $table->foreign('assigned_to_id')->references('id')->on('admins')->after('ticket_id');
            $table->foreign('assigned_by_id')->references('id')->on('admins')->after('assigned_to_id');
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->string('file')->nullable();
            $table->string('message');
            $table->unsignedBigInteger('commented_by_id');
            $table->timestamps();

            $table->foreign('ticket_id')->references('id')->on('tickets');
            $table->foreign('commented_by_id')->references('id')->on('admins')->after('ticket_id');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assigned_tickets', function (Blueprint $table) {
            $table->dropForeign(['ticket_id']);
            $table->dropForeign(['assigned_to_id']);
            $table->dropForeign(['assigned_by_id']);
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropForeign(['ticket_id']);
            $table->dropForeign(['commented_by_id']);
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['submitted_by']);
        });

        Schema::dropIfExists('assigned_tickets');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('tickets');
    }
};