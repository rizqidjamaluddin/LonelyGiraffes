<?php namespace Giraffe\Users;

use Eloquent;

class UserInterestModel extends Eloquent {
    protected $table = 'user_interests';
	protected $fillable = ['user_id', 'body'];
}