<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddUsersTableToNullableGender extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users', function(Blueprint $table)
		{
            $table->renameColumn('gender', 'tmp_gender');
		});
        Schema::table('users', function (Blueprint $table) {
            $table->string('gender')->nullable();
        });
        DB::statement("UPDATE `users` SET `gender` = `tmp_gender`;");
        Schema::table('users', function(Blueprint $table)
        {
            $table->dropColumn('tmp_gender');
        });
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
	}

}
