<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddHashColumnToBuddyRequests extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('buddy_requests', function(Blueprint $table)
		{
			$table->string('hash')->nullable()->unique('hash');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('buddy_requests', function(Blueprint $table)
		{
			$table->dropColumn('hash');
		});
	}

}
