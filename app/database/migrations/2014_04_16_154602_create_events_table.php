<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEventsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('events', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('hash', 32);
            $table->integer('user_id')->unsigned();
            $table->string('name');
            $table->text('body');
            $table->text('html_body');

            $table->string('url');
            $table->string('location');
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->decimal('lat', 18, 12)->nullable();
            $table->decimal('long', 18, 12)->nullable();
            $table->string('cell')->nullable();

            $table->timestamp('timestamp');

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
		Schema::drop('events');
	}

}
