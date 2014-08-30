<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class RenameCommentStreamTableToCommentStreams extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('comment_stream', function(Blueprint $table)
		{
			$table->rename('comment_streams');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('comment_streams', function(Blueprint $table)
		{
            $table->rename('comment_stream');
		});
	}

}
