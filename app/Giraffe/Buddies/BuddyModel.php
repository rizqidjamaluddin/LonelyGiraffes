<?php namespace Giraffe\Buddies;

use Eloquent;

class BuddyModel extends Eloquent {
    protected $table = 'buddies';
	protected $fillable = ['user_id', 'friend_id'];
}