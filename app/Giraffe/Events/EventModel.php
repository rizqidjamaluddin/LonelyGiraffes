<?php namespace Giraffe\Events;

use Eloquent;
use Giraffe\Common\HasEloquentHash;

class EventModel extends Eloquent {
    use HasEloquentHash;

    protected $table = 'events';
	protected $fillable = ['hash', 'user_id', 'name', 'body', 'html_body', 'location', 'city', 'state', 'country', 'lat', 'long',
        'cell', 'timestamp'];
}