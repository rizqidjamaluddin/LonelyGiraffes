<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddHashColumnToChatMessages extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('chat_messages', function(Blueprint $table)
		{
			$table->string('hash')->unique();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('chat_messages', function(Blueprint $table)
		{
			
		});
	}

}
