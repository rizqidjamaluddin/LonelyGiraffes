<?php  namespace Giraffe\BuddyRequests;


use Giraffe\Buddies\BuddyRepository;
use Giraffe\Buddies\BuddyService;
use Giraffe\Common\NotFoundModelException;
use Giraffe\Common\NotImplementedException;
use Giraffe\Common\Service;
use Giraffe\Users\UserRepository;
use Illuminate\Support\Collection;
use Str;

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
    private $buddyRequestService;
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

    public function check($user1, $user2)
    {
        // $this->gatekeeper->mayI
        $user1 = $this->userRepository->getByHash($user1);
        $user2 = $this->userRepository->getByHash($user2);
        return $this->buddyRequestRepository->getByPair($user1, $user2);
    }


    public function createBuddyRequest($userHash, $targetHash)
    {
        $this->gatekeeper->mayI('create', 'buddy_request')->please();

        $user = $this->userRepository->getByHash($userHash);
        $target = $this->userRepository->getByHash($targetHash);

        // make sure there's already a request
        try {
            $this->buddyRequestRepository->getByPair($user, $target);
            throw new ExistingBuddyRequestException;
        } catch (NotFoundModelException $e) {
            // continue
        }

        // make sure the users aren't already buddies
        if ($this->buddyService->checkBuddies($user, $target)) {
            throw new AlreadyBuddiesException("You're already buddies!");
        }

        $data = [];
        $data['from_user_id'] = $user->id;
        $data['to_user_id'] = $target->id;
        $data['hash'] = Str::random(32);

        $this->creationValidator->validate($data);

        $buddyRequest = $this->buddyRequestRepository->create($data);
        $this->log->info($this, 'Buddy Request created', $buddyRequest->toArray());
        return $buddyRequest->load(array('sender', 'recipient'));
    }

    public function getBuddyRequests($userHash)
    {
        $user = $this->userRepository->getByHash($userHash);
        $this->gatekeeper->mayI('read_buddy_request', $user)->please();
        return $this->buddyRequestRepository->getReceivedByUser($user);
    }

    public function getOutgoingBuddyRequestBetweenUsers($userHash, $userFilter)
    {
        $sender = $this->userRepository->getByHash($userHash);
        $receiver = $this->userRepository->getByHash($userFilter);
        try {
            return new Collection([$this->buddyRequestRepository->getBySenderAndReceiver($sender, $receiver)]);
        } catch (NotFoundModelException $e) {
            return new Collection;
        }
    }

    public function getOutgoingBuddyRequests($userHash)
    {
        $user = $this->userRepository->getByHash($userHash);
        return $this->buddyRequestRepository->getSentByUser($user);
    }

    public function acceptBuddyRequest($me, $targetHash)
    {
        $request = $this->buddyRequestRepository->getByHash($targetHash);
        $user = $this->userRepository->get($me);
        $this->gatekeeper->mayI('accept', $request)->please();
        $this->gatekeeper->mayI('add_buddy', $user)->please();

        $this->buddyRequestRepository->delete($request);
        return $this->buddyService->createBuddy($request);
    }

    public function denyBuddyRequest($targetHash)
    {
        $request = $this->buddyRequestRepository->getByHash($targetHash);
        $this->gatekeeper->mayI('delete', $request)->please();

        $this->buddyRequestRepository->delete($request);
    }

    public function getBuddyRequestsBetweenUsers($userHash, $userFilter)
    {
        $receiver = $this->userRepository->getByHash($userHash);
        $sender = $this->userRepository->getByHash($userFilter);

        $request = $this->buddyRequestRepository->getBySenderAndReceiver($sender, $receiver);
        $this->gatekeeper->mayI('read', $request)->please();

        try {
            $result = new Collection([$request]);
        } catch (NotFoundModelException $e) {
            $result = new Collection;
        }

        return $result;
    }
} 