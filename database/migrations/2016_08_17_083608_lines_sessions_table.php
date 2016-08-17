<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LinesSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('line_sessions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('line_account_id');
            $table->string('access_token')->unique();
            $table->string('token_type');
            $table->string('expires_in');
            $table->string('refresh_token');
            $table->string('scope');
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
        Schema::drop('line_sessions');
    }
}
