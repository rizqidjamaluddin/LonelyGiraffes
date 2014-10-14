<?php namespace Giraffe\Events;

use Eloquent;
use Giraffe\Support\Transformer\Transformable;

class EventAttendeeModel extends Eloquent implements Transformable {
    protected $table = 'event_attendees';
	protected $fillable = ['user_id', 'event_id', 'method'];
}