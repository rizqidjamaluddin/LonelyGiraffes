<?php namespace Giraffe\Chat;

use Eloquent;
use Giraffe\Authorization\ProtectedResource;
use Giraffe\Common\HasEloquentHash;
use Giraffe\Users\UserModel;
use Giraffe\Users\UserRepository;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use Illuminate\Support\Collection;

class ChatroomModel extends Eloquent implements ProtectedResource
{
    use HasEloquentHash, SoftDeletingTrait;

    protected $table = 'chatrooms';
    protected $fillable = ['name', 'hash'];

    public function memberships()
    {
        return $this->hasMany('\Giraffe\Chat\ChatroomMembershipModel', 'conversation_id');
    }

    /**
     * @return Collection;
     */
    public function participants()
    {
        return $this->memberships()->with('user')->get();
    }

    public function participantUserIDs()
    {
        $userIds = $this->memberships()->lists('user_id');
        return $userIds;
    }

    /**
     * Lowercase name of this resource.
     *
     * @return string
     */
    public function getResourceName()
    {
        return 'chatroom';
    }

    /**
     * @param \Giraffe\Users\UserModel $user
     *
     * @return bool
     */
    public function checkOwnership(UserModel $user)
    {
        return in_array($user->id, $this->participantUserIDs());
    }
}