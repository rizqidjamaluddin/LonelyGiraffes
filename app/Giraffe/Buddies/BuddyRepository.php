<?php  namespace Giraffe\Buddies;

use Giraffe\Common\EloquentRepository;
use Giraffe\Common\NotFoundModelException;

class BuddyRepository extends EloquentRepository
{

    public function __construct(UserRepository $userRepository,
                                BuddyModel $buddyModel)
    {
        parent::__construct($buddyModel);
    }

    /**
     * Gets Buddy relationships for a user.
     *
     * @param string|BuddyModel $user
     *
     * @throws \Giraffe\Common\NotFoundModelException
     * @return BuddyModel
     */
    public function getByUser($user)
    {
        if ($user instanceof BuddyModel) {
            return $user;
        }

        $model = $this->model->where('user1_id', '=', $user->id)->orWhere('user2_id', '=', $user->id);

        if ($model->isEmpty()) {
            throw new NotFoundModelException();
        }
        return $model;
    }

    /**
     * Gets a Buddy relationship of two users.
     *
     * @param \Eloquent|int $user
     * @param \Eloquent|int $friend
     * @return BuddyModel|null
     * @throws NotFoundModelException
     */
    public function getFromPair($user, $friend){

        $model = $this->model
            ->where('user1_id', '=', $user->id)->where('user2_id', '=', $friend->id)
            ->orWhere('user1_id', '=', $friend->id)->where('user2_id', '=', $user->id)
            ->first();
        if(!$model)
            throw new NotFoundModelException();

        return $model;
    }
} 