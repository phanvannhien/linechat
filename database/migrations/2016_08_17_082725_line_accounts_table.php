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
            $table->string('user_id');
            $table->string('mid')->unique();
            $table->string('displayName');
            $table->string('pictureUrl');
            $table->string('statusMessage');
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
