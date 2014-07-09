<?php namespace Giraffe\Buddies;

use Eloquent;

class BuddyModel extends Eloquent {
    protected $table = 'buddies';
	protected $fillable = ['user1_id', 'user1_id'];

    public function getResourceName()
    {
        return "buddy";
    }
}