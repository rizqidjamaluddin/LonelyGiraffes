<?php namespace Giraffe\Shouts;

use Eloquent;
use Giraffe\Contracts\Postable;

class ShoutModel extends Eloquent implements Postable {
    protected $table = 'shouts';
	protected $fillable = ['hash', 'user_id', 'name', 'body', 'html_body'];
}