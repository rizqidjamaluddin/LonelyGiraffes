<?php namespace Giraffe\Buddies;

use Eloquent;
use Giraffe\Support\Transformer\Transformable;

class BuddyModel extends Eloquent implements Transformable {
    protected $table = 'buddies';
	protected $fillable = ['user1_id', 'user2_id'];

    public function getResourceName()
    {
        return "buddy";
    }

    public function users() {
        return $this
            ->leftJoin('users', function($join)
            {
                $join->on('users.id', '=', 'user1_id')->orOn('users.id', '=', 'user2_id');
            });
    }
}