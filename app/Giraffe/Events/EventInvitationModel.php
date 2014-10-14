<?php namespace Giraffe\Events;

use Eloquent;
use Giraffe\Common\HasEloquentHash;
use Giraffe\Support\Transformer\Transformable;

class EventInvitationModel extends Eloquent implements Transformable {
    use HasEloquentHash;

    protected $table = 'event_invitations';
	protected $fillable = ['hash', 'user_id', 'event_id', 'sender_id'];
}