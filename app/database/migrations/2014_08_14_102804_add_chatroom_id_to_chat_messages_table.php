<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddChatroomIdToChatMessagesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('chat_messages', function(Blueprint $table)
		{
			$table->integer('chatroom_id', false, true)->nullable();
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
            $table->dropColumn('chatroom_id');
		});
	}

}
