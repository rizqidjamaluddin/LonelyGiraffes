<?php  namespace Giraffe\Buddies\Requests;

use Eloquent;
use Giraffe\Authorization\ProtectedResource;
use Giraffe\Common\HasEloquentHash;
use Giraffe\Users\UserModel;

class BuddyRequestModel extends Eloquent implements ProtectedResource {
    use HasEloquentHash;

    protected $table = 'buddy_requests';
	protected $fillable = ['hash', 'from_user_id', 'to_user_id'];

    public function getResourceName()
    {
        return "buddy_request";
    }

    public function recipient()
    {
        return $this->belongsTo('Giraffe\Users\UserModel', 'to_user_id');
    }

    public function sender()
    {
        return $this->belongsTo('Giraffe\Users\UserModel', 'from_user_id');
    }

    /**
     * @param \Giraffe\Users\UserModel $user
     *
     * @return bool
     */
    public function checkOwnership(UserModel $user)
    {
        return $user->id === $this->sender->id || $user->id === $this->recipient->id;
    }
}