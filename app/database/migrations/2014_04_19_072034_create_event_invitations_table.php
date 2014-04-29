<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEventInvitationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('event_invitations', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('hash', 32);
            $table->integer('event_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->integer('sender_id')->unsigned();
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('event_invitations');
	}

}
