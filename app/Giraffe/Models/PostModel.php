<?php namespace Giraffe\Models;

use Eloquent;
use Giraffe\Support\HasHashEloquent;

class PostModel extends Eloquent {
    use HasHashEloquent;

    protected $table = 'posts';
	protected $fillable = ['user_id', 'postable_type', 'postable_id', 'city', 'state', 'country', 'lat', 'long', 'cell'];
}