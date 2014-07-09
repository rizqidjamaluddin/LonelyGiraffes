<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBuddyRequestsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('buddy_requests', function(Blueprint $table)
		{
			$table->increments('id');

            $table->integer('from_user_id')->unsigned();
            $table->integer('to_user_id')->unsigned();
            $table->timestamp('sent_time');
            $table->timestamp('seen_time')->nullable();

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
		Schema::drop('buddy_requests');
	}

}
