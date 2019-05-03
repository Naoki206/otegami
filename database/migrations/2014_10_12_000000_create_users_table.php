<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::create('users', function (Blueprint $table) {
 			$table->increments('id');
			$table->string('name');
			$table->string('email', 191)->nullable();
			$table->string('password')->nullable();
			$table->string('twitter_name')->nullable();
			$table->string('twitter_id', 191)->nullable()->unique()->index();
			$table->string('access_token')->nullable();
			$table->string('access_token_secret')->nullable();
			$table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
