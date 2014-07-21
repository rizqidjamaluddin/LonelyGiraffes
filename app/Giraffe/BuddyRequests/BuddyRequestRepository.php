<?php  namespace Giraffe\BuddyRequests;

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
    public function destroyByPair($sender, $recipient) {
        $buddyRequest = $this->model->where('from_user_id', '=', $sender->id)->where('to_user_id', '=', $recipient->id)->first();

        if(!$buddyRequest)  {
            throw new NotFoundModelException();
        }

        $buddyRequest->delete();

        return $buddyRequest;
    }
} 