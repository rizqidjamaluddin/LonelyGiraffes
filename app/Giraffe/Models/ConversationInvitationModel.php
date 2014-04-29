<?php namespace Giraffe\Models;

use Eloquent;

class ConversationInvitationModel extends Eloquent {
    protected $table = 'conversation_invitations';
	protected $fillable = ['hash', 'conversation_id', 'user_id', 'invitee_id'];
}