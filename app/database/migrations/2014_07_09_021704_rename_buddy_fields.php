<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameBuddyFields extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('buddies', function($table) {
            $table->renameColumn('user_id', 'user1_id');
        });
        Schema::table('buddies', function($table) {
            $table->renameColumn('friend_id', 'user2_id');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('buddies', function($table) {
            $table->renameColumn('user1_id', 'user_id');
        });
        Schema::table('buddies', function($table) {
            $table->renameColumn('user2_id', 'friend_id');
        });
	}

}
