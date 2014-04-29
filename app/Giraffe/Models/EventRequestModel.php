<?php namespace Giraffe\Models;

use Eloquent;
use Giraffe\Support\HasHashEloquent;

class EventRequestModel extends Eloquent {
    use HasHashEloquent;

    protected $table = 'event_requests';
	protected $fillable = ['event_id', 'user_id', 'invitee_id'];
}