<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('docents', function (Blueprint $table) {

            $table->increments('id');
            $table->string('academic_title', 50);
            $table->string('firstname',50);
            $table->string('lastname', 50);
            $table->string('email');
            $table->unique('email');
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
        \Schema::dropIfExists('docents');
    }
}
