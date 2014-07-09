<?php  namespace Giraffe\BuddyRequests;


use Giraffe\Common\Service;

class BuddyRequestService extends Service
{
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
        BuddyRepository $userRepository,
        BuddyCreationValidator $creationValidator,
        BuddyUpdateValidator $updateValidator
    ) {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->creationValidator = $creationValidator;
        $this->updateValidator = $updateValidator;
    }


    public function createBuddyRequest($user_hash, $target_hash)
    {
        $this->gatekeeper->mayI('create', 'buddy_request')->please();

        //TODO: HOW DO I DO THIS
        $user = User.find($user_hash);
        $target = User.find($target_hash);

        $data = [];
        $data['from_user_id'] = $user->id;
        $data['to_user_id'] = $target->id;
        $data['sent_time'] = time();

        $this->creationValidator->validate($data);

        $buddy_request = $this->buddyRequestRepository->create($data);
        $this->log->info($this, 'Buddy Request created', $buddy_request->toArray());
        return $buddy_request;
    }

    public function acceptBuddyRequest($request_id)
    {
        $this->gatekeeper->mayI('destory', 'buddy_request')->please();
        $this->gatekeeper->mayI('create', 'buddy')->please();

    }

    public function denyBuddyRequest($request)
    {
        $this->gatekeeper->mayI('destory', 'buddy_request')->please();

    }

    public function removeBuddy($user)
    {

    }
} 