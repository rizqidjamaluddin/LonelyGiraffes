<?php  namespace Giraffe\Buddies;

use Giraffe\BuddyRequests\BuddyRequestModel;
use Giraffe\Common\NotFoundModelException;
use Giraffe\Common\Service;
use Giraffe\Users\UserRepository;

class BuddyService extends Service
{
    /**
     * @var \Giraffe\Users\UserRepository
     */
    private $userRepository;
    /**
     * @var \Giraffe\Buddies\BuddyRepository
     */
    private $buddyRepository;
    /**
     * @var BuddyCreationValidator
     */
    private $creationValidator;

    public function __construct(
        UserRepository $userRepository,
        BuddyRepository $buddyRepository,
        BuddyCreationValidator $creationValidator
    ) {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->buddyRepository = $buddyRepository;
        $this->creationValidator = $creationValidator;
    }

    public function getBuddies($userHash)
    {
        $user = $this->userRepository->getByHash($userHash);
        $this->gatekeeper->mayI('read_buddy', $user)->please();
        return $this->buddyRepository->getByUser($user);
    }

    public function checkBuddies($user1, $user2)
    {
        $user1 = $this->userRepository->get($user1);
        $user2 = $this->userRepository->get($user2);
        try {
            $check = $this->buddyRepository->getByPair($user1, $user2);
        } catch (NotFoundModelException $e) {
            return false;
        }
        return true;
    }

    public function unbuddy($userHash, $buddyHash)
    {
        $user = $this->userRepository->getByHash($userHash);
        $buddy = $this->userRepository->getByHash($buddyHash);
        $this->gatekeeper->mayI('delete_buddy', $user)->please();

        $this->buddyRepository->deleteByPair($user, $buddy);
    }

    /**
     * @param BuddyRequestModel $buddyRequest
     */
    public function createBuddy($buddyRequest)
    {
        $data = [];
        $data['user1_id'] = min($buddyRequest->from_user_id, $buddyRequest->to_user_id);
        $data['user2_id'] = max($buddyRequest->from_user_id, $buddyRequest->to_user_id);

        $this->creationValidator->validate($data);
        $buddy = $this->buddyRepository->create($data);

        return $buddy;
    }
} 