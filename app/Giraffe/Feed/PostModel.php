<?php namespace Giraffe\Feed;

use Eloquent;
use Giraffe\Common\HasEloquentHash;

class PostModel extends Eloquent {
    use HasEloquentHash;

    protected $table = 'posts';
	protected $fillable = ['user_id', 'postable_type', 'postable_id', 'city', 'state', 'country', 'lat', 'long', 'cell'];

    public function postable()
    {
        return $this->morphTo();
    }
}