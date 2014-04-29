<?php namespace Giraffe\Models;

use Eloquent;


class UserLocationModel extends Eloquent {
    protected $table = 'user_locations';
	protected $fillable = ['user_id', 'country', 'state', 'city', 'lat', 'long'];
}