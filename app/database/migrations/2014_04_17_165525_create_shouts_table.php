<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateShoutsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('shouts', function(Blueprint $table)
		{
			$table->bigIncrements('id');
            $table->string('hash', 32);
            $table->integer('user_id')->unsigned();
            $table->string('name');
            $table->text('body');
            $table->text('html_body');
			$table->timestamps();
            $table->softDeletes();

            $table->unique('hash');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('shouts');
	}

}
