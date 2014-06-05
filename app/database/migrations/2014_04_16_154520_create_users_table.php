<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('hash')->unique();
            $table->string('nickname')->nullable();
            $table->string('firstname');
            $table->string('lastname');
            $table->string('email');
            $table->string('password');
            $table->string('token')->nullable();

            $table->string('cell')->nullable();

            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
//            $table->decimal('lat', 18, 12)->nullable();
//            $table->decimal('long', 18, 12)->nullable();

            $table->timestamp('date_of_birth')->nullable();
            $table->enum('gender', ['M', 'F', 'X']);
			$table->timestamps();
            $table->softDeletes();

            $table->unique('email');
            $table->unique('token');

            $table->enum('role', ['member', 'mod', 'admin'])->default('member');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('users');
	}

}
