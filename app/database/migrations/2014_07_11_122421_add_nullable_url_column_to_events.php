<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddNullableUrlColumnToEvents extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('events', function(Blueprint $table)
            {
                $table->renameColumn('url', 'tmp_url');
            });
        Schema::table('events', function (Blueprint $table) {
                $table->string('url')->nullable();
            });
        DB::statement("UPDATE `events` SET `url` = `tmp_url`;");
        Schema::table('events', function(Blueprint $table)
            {
                $table->dropColumn('tmp_url');
            });
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('events', function(Blueprint $table)
		{
			
		});
	}

}
