<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserDisplayNameField extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        /**
         * Don't ask why
         * @see https://github.com/laravel/framework/issues/2979
         */
        Schema::table('users', function(Blueprint $table)
            {
                $table->renameColumn('nickname', 'name');
            });
        Schema::table('users', function(Blueprint $table)
            {
                $table->dropColumn('lastname');
            });
        Schema::table('users', function(Blueprint $table)
            {
                $table->dropColumn('firstname');
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
        });
	}

}
