<?php namespace Giraffe\Chat;

use \Eloquent;

class ChatroomMemberModel extends Eloquent {
    protected $table = 'conversation_members';
	protected $fillable = ['user_id', 'conversation_id'];
}