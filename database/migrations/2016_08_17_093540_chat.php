<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Chat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::create('messages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('room_id');
            $table->string('from_mid');
            $table->string('to_mid');
            $table->string('message');
            $table->timestamps();
            //$table->index(array('room_id','from_mid','to_mid'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('messages');
    }
}
