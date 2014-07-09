<?php  namespace Giraffe\Buddies;

use Giraffe\Common\EloquentRepository;
use Giraffe\Common\NotFoundModelException;

class BuddyRepository extends EloquentRepository
{

    public function __construct(BuddyModel $buddyModel)
    {
        parent::__construct($buddyModel);
    }

    /**
     * @param string|BuddyModel $user_hash
     *
     * @throws \Giraffe\Common\NotFoundModelException
     * @return BuddyModel
     */
    public function getByUser($user_hash)
    {
        if ($user_hash instanceof BuddyModel) {
            return $user_hash;
        }

        //!!! How do I find user?
        $user = $this->userRepository.getUser($user_hash);

        $model = $this->model->where('user1_id', '=', $user.id)->orWhere('user2_id', '=', $user->id);

        if ($model->isEmpty()) {
            throw new NotFoundModelException();
        }
        return $model;
    }

    /**
     * Extend the base get() method to accept a user's public_id
     *
     * @param \Eloquent|int $user_hash
     * @param \Eloquent|int $friend_hash
     * @return BuddyModel|null
     * @throws NotFoundModelException
     */
    public function getFromPair($user_hash, $friend_hash){

        if ($user_hash instanceof BuddyModel)
            return $user_hash;
        if ($friend_hash instanceof BuddyModel)
            return $friend_hash;

        //!!! How do I find user?
        $user = $this->userRepository->getUser($user_hash);
        $friend = $this->userRepository->getUser($friend_hash);

        $model = $this->model->
            where('user1_id', '=', $user->id)->where('user2_id', '=', $friend->id)
            ->orWhere('user1_id', '=', $friend->id)->where('user2_id', '=', $user->id)
            ->first();
        if(!$model)
            throw new NotFoundModelException();

        return $model;
    }
} 