<?php namespace Giraffe\Chat;

use \Eloquent;

class ChatroomMembershipModel extends Eloquent {
    protected $table = 'chatroom_memberships';
	protected $fillable = ['user_id', 'conversation_id'];
}