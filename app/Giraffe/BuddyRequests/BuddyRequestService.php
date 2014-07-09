<?php  namespace Giraffe\BuddyRequests;


use Giraffe\Common\Service;

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
     * @var BuddyRequestCreationValidator
     */
    private $creationValidator;
    /**
     * @var BuddyRequestUpdateValidator
     */
    private $updateValidator;

    public function __construct(
        UserRepository $userRepository,
        BuddyRepository $userRepository,
        BuddyCreationValidator $creationValidator,
        BuddyUpdateValidator $updateValidator
    ) {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->creationValidator = $creationValidator;
        $this->updateValidator = $updateValidator;
    }


    public function createBuddyRequest($userHash, $targetHash)
    {
        $this->gatekeeper->mayI('create', 'buddy_request')->please();

        $user = $this->userRepository->getByHash($userHash);
        $target = $this->userRepository->getByHash($targetHash);

        $data = [];
        $data['from_user_id'] = $user->id;
        $data['to_user_id'] = $target->id;
        $data['sent_time'] = time();

        $this->creationValidator->validate($data);

        $buddyRequest = $this->buddyRequestRepository->create($data);
        $this->log->info($this, 'Buddy Request created', $buddyRequest->toArray());
        return $buddyRequest;
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