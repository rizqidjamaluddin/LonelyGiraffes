<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateConversationInvitationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('conversation_invitations', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('hash', 32);
            $table->integer('conversation_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->integer('invitee_id')->unsigned();
			$table->timestamps();
            $table->unique('hash');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('conversation_invitations');
	}

}
