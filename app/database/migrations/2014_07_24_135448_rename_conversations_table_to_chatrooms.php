<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class RenameConversationsTableToChatrooms extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::rename('conversations', 'chatrooms');
		Schema::rename('conversation_members', 'chatroom_memberships');
		Schema::rename('conversation_messages', 'chat_messages');
        Schema::drop('conversation_invitations');
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
