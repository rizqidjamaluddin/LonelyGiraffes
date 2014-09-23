<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class UpdateNotificationContainersToRemoveNotificationId extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('notification_containers', function(Blueprint $table)
		{
			$table->dropColumn('notification_id');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('notification_containers', function(Blueprint $table)
		{
			$table->integer('notification_id');
		});
	}

}
