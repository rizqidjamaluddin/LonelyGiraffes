<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * This is a hack.
 *
 * Laravel's internals demand that user models MUST provide a column for "remember me" tokens, even if we never use
 * the feature. In particular, this makes it impossible, in unit tests, to switch from a user to a guest, as the
 * logout functionality fails.
 *
 * @see http://laravel.io/forum/05-21-2014-how-to-disable-remember-token
 */
class AddUserRememberMeColumnsForCompat extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users', function(Blueprint $table)
		{
			$table->string('unused_token')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('users', function(Blueprint $table)
		{
			$table->dropColumn('unused_token');
		});
	}

}
