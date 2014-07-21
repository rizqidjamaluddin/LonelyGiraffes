<?php  namespace Giraffe\BuddyRequests;


use Giraffe\Buddies\BuddyRepository;
use Giraffe\Buddies\BuddyService;
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
     * @var \Giraffe\Buddies\BuddyRepository
     */
    private $buddyRepository;
    /**
     * @var \Giraffe\BuddyRequests\BuddyRequestService
     */
    private $buddyRequestSerivce;
    /**
     * @var \Giraffe\BuddyRequests\BuddyRequestCreationValidator
     */
    private $creationValidator;

    public function __construct(
        UserRepository $userRepository,
        BuddyService $buddyService,
        BuddyRequestRepository $buddyRequestRepository,
        BuddyRequestCreationValidator $creationValidator
    ) {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->buddyService = $buddyService;
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

    public function getBuddyRequests($userHash)
    {
        //$this->gatekeeper->mayI('get', 'buddy_request')->please();
        $user = $this->userRepository->getByHash($userHash);
        return $this->buddyRequestRepository->getReceivedByUser($user);
    }

    public function getOutgoingBuddyRequests($userHash)
    {
        $user = $this->userRepository->getByHash($userHash);
        return $this->buddyRequestRepository->getSentByUser($user);
    }

    public function acceptBuddyRequest($userHash, $targetHash)
    {
        //$this->gatekeeper->mayI('destroy', 'buddy_request')->please();
        //$this->gatekeeper->mayI('create', 'buddy')->please();
        $user = $this->userRepository->getByHash($userHash);
        $sender = $this->userRepository->getByHash($targetHash);

        $request = $this->buddyRequestRepository->destroyByPair($sender, $user);
        return $this->buddyService->createBuddy($request);
    }

    public function denyBuddyRequest($userHash, $targetHash)
    {
        //$this->gatekeeper->mayI('destroy', 'buddy_request')->please();
        $user = $this->userRepository->getByHash($userHash);
        $sender = $this->userRepository->getByHash($targetHash);

        $this->buddyRequestRepository->destroyByPair($sender, $user);
    }
} 