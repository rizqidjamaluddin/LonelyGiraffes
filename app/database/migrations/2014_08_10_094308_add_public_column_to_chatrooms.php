<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddPublicColumnToChatrooms extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('chatrooms', function(Blueprint $table)
		{
            $table->boolean('public')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('chatrooms', function(Blueprint $table)
		{
			$table->dropColumn('public');
		});
	}

}
