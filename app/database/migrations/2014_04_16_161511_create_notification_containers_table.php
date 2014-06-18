<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateNotificationContainersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('notification_containers', function(Blueprint $table)
		{
			$table->bigIncrements('id');
            $table->integer('user_id')->unsigned();
            $table->string('hash', 32);
            $table->string('notification_type');
            $table->bigInteger('notification_id');
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
		Schema::drop('notifications_containers');
	}

}
