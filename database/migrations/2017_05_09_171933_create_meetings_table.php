<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMeetingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meetings', function (Blueprint $table) {

            $table->increments('id');
            $table->integer('docent_id')->unsigned();
            $table->foreign('docent_id')->references('id')->on('docents');
            $table->dateTime('start');
            $table->dateTime('end');
            $table->integer('slots');
            $table->boolean('email_notification_docent');
            $table->string('title', 50);
            $table->string('description_public', 500);
            $table->string('description_private', 500);
            $table->string('room', 10);
            $table->dateTime('last_enrollment');
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
        Schema::dropIfExists('meetings');
    }
}
