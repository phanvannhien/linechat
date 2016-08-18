<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LineAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
         Schema::create('line_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('mid')->unique();// Line ID
            $table->string('displayName')->nullable();
            $table->string('pictureUrl')->nullable();
            $table->string('statusMessage')->nullable();
            $table->string('access_token')->unique();
            $table->string('token_type');
            $table->string('expires_in');
            $table->string('refresh_token');
            $table->string('scope')->nullable();
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
         Schema::drop('line_accounts');
    }
}
