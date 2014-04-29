<?php namespace Giraffe\Models;

use Eloquent;
use Giraffe\Support\HasHashEloquent;

class EventInvitationModel extends Eloquent {
    use HasHashEloquent;

    protected $table = 'event_invitations';
	protected $fillable = ['hash', 'user_id', 'event_id', 'sender_id'];
}