<?php namespace Giraffe\Chat;

use \Eloquent;

class ChatroomMembershipModel extends Eloquent {
    protected $table = 'chatroom_memberships';
	protected $fillable = ['user_id', 'conversation_id'];

    public function user()
    {
        return $this->belongsTo('\Giraffe\Users\UserModel', 'user_id');
    }
}