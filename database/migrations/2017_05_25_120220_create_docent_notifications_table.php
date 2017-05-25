<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocentNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('docent_notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('docent_id')->unsigned();
            $table->foreign('docent_id')->references('id')->on('docents')->onDelete('cascade');
            $table->integer('message')->unsigned();
            $table->foreign('message')->references('id')->on('notification_messages')->onDelete('cascade');
            $table->boolean('seen');
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
        Schema::dropIfExists('docent_notifications');
    }
}
