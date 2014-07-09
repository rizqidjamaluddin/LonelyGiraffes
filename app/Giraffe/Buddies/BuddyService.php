<?php  namespace Giraffe\Buddies;

use Giraffe\Common\Service;
use Giraffe\Buddies\BuddyModel;

class BuddyService extends Service
{
    /**
     * @var \Giraffe\Buddies\BuddyRepository
     */
    private $buddyRepository;
    /**
     * @var BuddyCreationValidator
     */
    private $creationValidator;
    /**
     * @var BuddyUpdateValidator
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

    public function getBuddies($user)
    {
        $this->gatekeeper->mayI('get', 'buddies')->please();
        return $this->buddyRepository->getByUser($user);
    }

    public function unbuddy($user, $buddy)
    {

    }
} 