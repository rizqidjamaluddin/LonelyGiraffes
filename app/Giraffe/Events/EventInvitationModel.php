<?php namespace Giraffe\Events;

use Eloquent;
use Giraffe\Common\HasEloquentHash;

class EventInvitationModel extends Eloquent {
    use HasEloquentHash;

    protected $table = 'event_invitations';
	protected $fillable = ['hash', 'user_id', 'event_id', 'sender_id'];
}