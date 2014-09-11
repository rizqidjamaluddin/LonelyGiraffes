<?php  namespace Giraffe\Buddies\BuddyRequests;

use Giraffe\Buddies\BuddyRequests\BuddyRequestModel;
use Giraffe\Common\EloquentRepository;
use Giraffe\Common\NotFoundModelException;
use Giraffe\Users\UserModel;
use Illuminate\Database\Eloquent\Collection;

class BuddyRequestRepository extends EloquentRepository
{

    public function __construct(BuddyRequestModel $buddyRequestModel)
    {
        parent::__construct($buddyRequestModel);
    }

    public function getByPair(UserModel $user1, UserModel $user2)
    {
        $buddyRequest = $this->model->where('from_user_id', '=', $user1->id)->where(
            'to_user_id',
            '=',
            $user2->id
        )->first();

        if (!$buddyRequest) {
            $buddyRequest = $this->model->where('from_user_id', '=', $user2->id)->where(
                'to_user_id',
                '=',
                $user1->id
            )->first();

            if (!$buddyRequest) {
                throw new NotFoundModelException();
            }
        }

        return $buddyRequest;
    }

    /**
     * Gets Buddy requests (with their intended recipients) sent by a user
     *
     * @param UserModel $user
     *
     * @throws \Giraffe\Common\NotFoundModelException
     * @return Collection
     */
    public function getSentByUser($user)
    {
        if ($user instanceof BuddyRequestModel) {
            return $user;
        }

        $buddyRequests = $this->model->with('sender')->with('recipient')->where('from_user_id', '=', $user->id)->get();

        return $buddyRequests;
    }

    public function getBySenderAndReceiver(UserModel $sender, UserModel $receiver)
    {
        $result = $this->model->with(['sender', 'recipient'])->where('from_user_id', $sender->id)
                              ->where('to_user_id', $receiver->id)->first();

        if (!$result) {
            throw new NotFoundModelException;
        }
        return $result;
    }

    /**
     * Gets Buddy requests (with their original sender) sent to a user
     *
     * @param UserModel $user
     *
     * @throws \Giraffe\Common\NotFoundModelException
     * @return Collection
     */
    public function getReceivedByUser($user)
    {
        if ($user instanceof BuddyRequestModel) {
            return $user;
        }

        $buddyRequests = $this->model->with('sender')->with('recipient')->where('to_user_id', '=', $user->id)->get();

        return $buddyRequests;
    }

    /**
     * Destroys a Request based on the two users
     *
     * @param UserModel $sender
     * @param UserModel $recipient
     *
     * @throws \Giraffe\Common\NotFoundModelException
     * @return BuddyRequestModel
     */
    public function destroyByPair($sender, $recipient)
    {
        $buddyRequest = $this->model->where('from_user_id', '=', $sender->id)->where(
            'to_user_id',
            '=',
            $recipient->id
        )->first();

        if (!$buddyRequest) {
            throw new NotFoundModelException();
        }

        $buddyRequest->delete();

        return $buddyRequest;
    }
} 