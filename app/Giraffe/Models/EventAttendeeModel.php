<?php namespace Giraffe\Models;

use Eloquent;

class EventAttendeeModel extends Eloquent {
    protected $table = 'event_attendees';
	protected $fillable = ['user_id', 'event_id', 'method'];
}