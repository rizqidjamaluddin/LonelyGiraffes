<?php  namespace Giraffe\Buddies;

use Giraffe\Common\EloquentRepository;
use Giraffe\Common\NotFoundModelException;
use Illuminate\Support\Facades\DB;

class BuddyRepository extends EloquentRepository
{

    public function __construct(BuddyModel $buddyModel)
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

        // God help me for this.
        $models = DB::table('users as u')
            ->leftJoin('buddies as b1', 'u.id', '=', 'b1.user1_id')
            ->leftJoin('users as u1', 'u1.id', '=', 'b1.user2_id')
            ->leftJoin('buddies as b2', 'u.id', '=', 'b2.user2_id')
            ->leftJoin('users as u2', 'u2.id', '=', 'b2.user1_id')
            ->select('u.*')
            ->distinct()
            ->where('u1.id', '=', $user->id)->orWhere('u2.id', '=', $user->id)
            ->get();

        if (count($models)==0) {
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