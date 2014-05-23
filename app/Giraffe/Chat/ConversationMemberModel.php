<?php namespace Giraffe\Chat;

use \Eloquent;

class ConversationMemberModel extends Eloquent {
    protected $table = 'conversation_members';
	protected $fillable = ['user_id', 'conversation_id'];
}