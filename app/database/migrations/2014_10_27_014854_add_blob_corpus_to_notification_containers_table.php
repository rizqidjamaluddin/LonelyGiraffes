<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddBlobCorpusToNotificationContainersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::table('notification_containers', function(Blueprint $table)
                {
                    $table->renameColumn('corpus', 'tmp_corpus');
                });
            Schema::table('notification_containers', function (Blueprint $table) {
                    $table->binary('corpus')->nullable();
                });
            DB::statement("UPDATE `notification_containers` SET `corpus` = `tmp_corpus`;");
            Schema::table('notification_containers', function(Blueprint $table)
                {
                    $table->dropColumn('tmp_corpus');
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
