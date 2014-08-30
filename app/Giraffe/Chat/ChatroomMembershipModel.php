<?php namespace Giraffe\Chat;

use \Eloquent;
use Giraffe\Authorization\ProtectedResource;
use Giraffe\Users\UserModel;

/**
 * @property int $user_id
 * @property int $conversation_id
 */
class ChatroomMembershipModel extends Eloquent implements ProtectedResource {
    protected $table = 'chatroom_memberships';
	protected $fillable = ['user_id', 'conversation_id'];

    public function user()
    {
        return $this->belongsTo('\Giraffe\Users\UserModel', 'user_id');
    }

    /**
     * Lowercase name of this resource.
     *
     * @return string
     */
    public function getResourceName()
    {
        return 'chatroom_membership';
    }

    /**
     * @param \Giraffe\Users\UserModel $user
     *
     * @return bool
     */
    public function checkOwnership(UserModel $user)
    {
        return $this->user_id === $user->id;
    }
}