<?php  namespace Giraffe\Buddies\Requests;

use Eloquent;
use Giraffe\Authorization\ProtectedResource;
use Giraffe\Common\HasEloquentHash;
use Giraffe\Users\UserModel;
use Giraffe\Users\UserRepository;

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
        /** @var UserRepository $userRepository */
        $userRepository = \App::make(UserRepository::class);

        return $userRepository->getById($this->to_user_id);
    }

    public function sender()
    {
        /** @var UserRepository $userRepository */
        $userRepository = \App::make(UserRepository::class);

        return $userRepository->getById($this->from_user_id);
    }

    /**
     * @param \Giraffe\Users\UserModel $user
     *
     * @return bool
     */
    public function checkOwnership(UserModel $user)
    {
        return $user->id === $this->sender()->id || $user->id === $this->recipient()->id;
    }
}