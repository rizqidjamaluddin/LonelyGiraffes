<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImageTables extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function(Blueprint $table)
        {
            $table->increments('id');

            $table->integer('user_id')->unsigned();
            $table->string('hash');
            $table->string('extension');
            $table->integer('image_type_id')->unsigned();

            $table->timestamps();

            $table->unique(array('user_id', 'hash'));
        });

        Schema::create('image_types', function(Blueprint $table)
        {
            $table->increments('id');

            $table->string('name')->unique();
            $table->boolean('unique_per_user');

            $table->timestamps();
        });

    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('images');
        Schema::drop('image_types');
    }

}
