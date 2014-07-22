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
    /**
     * @var UserModel
     */
    private $userModel;

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

        return $models;
    }

    /**
     * Gets a Buddy relationship of two users.
     *
     * @param \Eloquent|int $user
     * @param \Eloquent|int $buddy
     * @return BuddyModel|null
     * @throws NotFoundModelException
     */
    public function getByPair($user, $buddy){

        $model = $this->model
            ->where('user1_id', '=', $user->id)->where('user2_id', '=', $buddy->id)
            ->orWhere('user1_id', '=', $buddy->id)->where('user2_id', '=', $user->id)
            ->first();
        if(!$model)
            throw new NotFoundModelException();

        return $model;
    }

    /**
     * @param \Eloquent|int $user
     * @param \Eloquent|int $buddy
     * @return void
     * @throws NotFoundModelException
     */
    public function deleteByPair($user, $buddy) {
        $this->getByPair($user, $buddy)->delete();
    }
} 