<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMeetingSeriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('meeting_series', function (Blueprint $table) {

            $table->increments('id');
            $table->integer('docent_id')->unsigned();
            $table->foreign('docent_id')->references('id')->on('docents')->onDelete('cascade');
            $table->index('docent_id');
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
        \Schema::dropIfExists('meeting_series');
    }
}
