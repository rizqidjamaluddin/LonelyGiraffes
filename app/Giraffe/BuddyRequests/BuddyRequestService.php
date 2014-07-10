<?php  namespace Giraffe\BuddyRequests;


use Giraffe\Common\NotImplementedException;
use Giraffe\Common\Service;
use Giraffe\Users\UserRepository;

class BuddyRequestService extends Service
{
    /**
     * @var \Giraffe\Users\UserRepository
     */
    private $userRepository;
    /**
     * @var \Giraffe\BuddyRequests\BuddyRequestRepository
     */
    private $buddyRequestRepository;
    /**
     * @var \Giraffe\BuddyRequests\BuddyRequestCreationValidator
     */
    private $creationValidator;

    public function __construct(
        UserRepository $userRepository,
        BuddyRequestRepository $buddyRequestRepository,
        BuddyRequestCreationValidator $creationValidator
    ) {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->buddyRequestRepository = $buddyRequestRepository;
        $this->creationValidator = $creationValidator;
    }


    public function createBuddyRequest($userHash, $targetHash)
    {
        //$this->gatekeeper->mayI('create', 'buddy_request')->please();

        $user = $this->userRepository->getByHash($userHash);
        $target = $this->userRepository->getByHash($targetHash);

        $data = [];
        $data['from_user_id'] = $user->id;
        $data['to_user_id'] = $target->id;
        $data['sent_time'] = time();

        $this->creationValidator->validate($data);

        $buddyRequest = $this->buddyRequestRepository->create($data);
        $this->log->info($this, 'Buddy Request created', $buddyRequest->toArray());
        return $buddyRequest->load(array('sender', 'recipient'));
    }

    public function getBuddyRequests($userHash, $sentOrReceived)
    {
        $user = $this->userRepository->getByHash($userHash);

        if($sentOrReceived=="sent")
            return $this->buddyRequestRepository->getSentByUser($user);
        elseif($sentOrReceived=="received")
            return $this->buddyRequestRepository->getReceivedByUser($user);
        else
            throw new NotImplementedException();
    }

    public function acceptBuddyRequest($request_id)
    {
        $this->gatekeeper->mayI('destroy', 'buddy_request')->please();
        $this->gatekeeper->mayI('create', 'buddy')->please();

    }

    public function denyBuddyRequest($request_id)
    {
        $this->gatekeeper->mayI('destroy', 'buddy_request')->please();

    }

    public function removeBuddy($user)
    {
        $this->gatekeeper->mayI('destroy', 'buddy')->please();
    }
} 