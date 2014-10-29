<?php  namespace Giraffe\Buddies;

use Giraffe\Common\DuplicateCreationException;
use Giraffe\Common\EloquentRepository;
use Giraffe\Common\InvalidCreationException;
use Giraffe\Common\NotFoundModelException;
use Giraffe\Users\UserRepository;
use Giraffe\Users\UserModel;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;

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

    public function __construct(
        BuddyModel $buddyModel,
        UserRepository $userRepository
    ) {
        parent::__construct($buddyModel);
    }

    public function create(array $attributes)
    {
        $this->getCache()->tags(['buddies:' . $attributes['user1_id']])->flush();
        $this->getCache()->tags(['buddies:' . $attributes['user2_id']])->flush();
        return parent::create($attributes);
    }


    /**
     * Gets Buddy relationships for a user.
     *
     * @param string|BuddyModel|UserModel $user
     *
     * @throws \Giraffe\Common\NotFoundModelException
     * @return Array
     */
    public function getByUser($user)
    {

        if ($user instanceof BuddyModel) {
            return $user;
        }

        $users = $this->model->where('user1_id', '=', $user->id)->orWhere('user2_id', '=', $user->id)->get(['user1_id', 'user2_id']);

        // short circuit early if no buddies found
        if ($users->count() === 0) {
            return new Collection;
        }

        // Flatten results array into ids, picking the one that IS NOT $user's.
        $users = $users->map(
                       function ($u) use ($user) {
                           if ($u->user1_id == $user->id) {
                               return $u->user2_id;
                           }
                           return $u->user1_id;
                       }
        );

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
    public function getByPair($user, $buddy)
    {

        $model = $this->model
            ->where('user1_id', '=', $user->id)->where('user2_id', '=', $buddy->id)
            ->orWhere('user1_id', '=', $buddy->id)->where('user2_id', '=', $user->id)
            ->cacheTags(['buddies:' . $user->id, 'buddies:' . $buddy->id])
            ->remember(20)
            ->first();
        if (!$model) {
            throw new NotFoundModelException();
        }

        return $model;
    }

    /**
     * @param \Eloquent|int $user
     * @param \Eloquent|int $buddy
     * @return void
     * @throws NotFoundModelException
     */
    public function deleteByPair($user, $buddy)
    {
        $this->getByPair($user, $buddy)->delete();
        $this->getCache()->tags(['buddies:' . $user->id])->flush();
        $this->getCache()->tags(['buddies:' . $buddy->id])->flush();
    }
} 