<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddFlagColumnToChatMemberships extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('chatroom_memberships', function(Blueprint $table)
		{
			$table->string('flag')->default('');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('chatroom_memberships', function(Blueprint $table)
		{
			$table->dropColumn('flag');
		});
	}

}
