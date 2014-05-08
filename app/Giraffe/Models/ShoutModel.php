<?php namespace Giraffe\Models;

use Eloquent;

class ShoutModel extends Eloquent {
    protected $table = 'shouts';
	protected $fillable = ['hash', 'user_id', 'name', 'body', 'html_body'];
}