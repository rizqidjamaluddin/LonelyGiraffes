<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class DeleteBuddyRequestSentNotificationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::drop('buddy_request_sent_notifications');
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::create('buddy_request_sent_notifications', function(Blueprint $table)
		{
            $table->increments('id');
            $table->integer('buddy_request_id')->unsigned();
            $table->timestamps();
		});
	}

}
