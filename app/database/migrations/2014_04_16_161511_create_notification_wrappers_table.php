<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateNotificationWrappersTable extends Migration {

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
            $table->string('metadata_type');
            $table->bigInteger('metadata_id');
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
