<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePostsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('posts', function(Blueprint $table)
		{
			$table->bigIncrements('id');
            $table->string('hash', 32);
            $table->integer('user_id')->unsigned();
            $table->string('postable_type');
            $table->bigInteger('postable_id')->unsigned();
			$table->timestamps();
            $table->softDeletes();

            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->decimal('lat', 18, 12)->nullable();
            $table->decimal('long', 18, 12)->nullable();
            $table->string('cell')->nullable();


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
		Schema::drop('posts');
	}

}
