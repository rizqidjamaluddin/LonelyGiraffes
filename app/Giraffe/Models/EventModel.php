<?php namespace Giraffe\Models;

use Eloquent;
use Giraffe\Support\HasHashEloquent;

class EventModel extends Eloquent {
    use HasHashEloquent;

    protected $table = 'events';
	protected $fillable = ['hash', 'user_id', 'name', 'body', 'html_body', 'location', 'city', 'state', 'country', 'lat', 'long',
        'cell', 'timestamp'];
}