<?php  namespace Giraffe\Buddies;

use Giraffe\Common\EloquentRepository;
use Giraffe\Common\NotFoundModelException;
use Giraffe\Users\UserRepository;
use Giraffe\Users\UserModel;

class BuddyRepository extends EloquentRepository
{

    /**
     * @var \Giraffe\Users\UserRepository
     */
    private $userRepository;
    public function __construct(BuddyModel $buddyModel,
                                UserRepository $userRepository)
    {
        parent::__construct($buddyModel);

    }

    /**
     * Gets Buddy relationships for a user.
     *
     * @param string|BuddyModel $user
     *
     * @throws \Giraffe\Common\NotFoundModelException
     * @return Array
     */
    public function getByUser($user)
    {
        if ($user instanceof BuddyModel) {
            return $user;
        }

        $users = $this->model->where('user1_id', '=', $user->id)->orWhere('user2_id', '=', $user->id)->get(array('user1_id', 'user2_id'));

        // Flatten results array into ids, picking the one that IS NOT $user's.
        $users = $users->map(function($u) use ($user) {
            if($u->user1_id == $user->id)
                return $u->user2_id;
            return $u->user1_id;
        });
        $models = UserModel::whereIn('id', $users->toArray())->get();


        if ($models->isEmpty()) {
            throw new NotFoundModelException();
        }
        return $models;
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