<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSeriesFkToMeetingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::table('meetings', function (Blueprint $table)
        {
           $table->integer('meeting_series_id')->unsigned();
           $table->foreign('meeting_series_id')->references('id')->on('meeting_series')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       \Schema::table('meetings', function (Blueprint $table) {

           $table->dropForeign(['meeting_series_id']);
           $table->dropColumn('meeting_series_id');
       });
    }
}
