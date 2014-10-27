<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddNotificationCorpusColumnToLongerLimitInNotificationsContainerTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('notification_containers', function(Blueprint $table)
            {
                $table->dropColumn('corpus');
            });
        Schema::table('notification_containers', function(Blueprint $table)
            {
                $table->text('corpus', 60000)->nullable();
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
			
		});
	}

}
