<?php namespace Giraffe\Models;

use Eloquent;

class UserInterestModel extends Eloquent {
    protected $table = 'user_interests';
	protected $fillable = ['user_id', 'body'];
}