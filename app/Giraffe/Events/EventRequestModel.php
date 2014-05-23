<?php namespace Giraffe\Events;

use Eloquent;
use Giraffe\Common\HasEloquentHash;

class EventRequestModel extends Eloquent {
    use HasEloquentHash;

    protected $table = 'event_requests';
	protected $fillable = ['event_id', 'user_id', 'invitee_id'];
}