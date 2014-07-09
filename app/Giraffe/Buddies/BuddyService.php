<?php  namespace Giraffe\Buddies;

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
        BuddyRepository $userRepository,
        BuddyCreationValidator $creationValidator
    ) {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->creationValidator = $creationValidator;
    }

    public function getBuddies($userHash)
    {
        $user = $this->userRepository->getByHash($userHash);
        $this->gatekeeper->mayI('get', 'buddies')->please();
        return $this->buddyRepository->getByUser($user);
    }

    public function unbuddy($userHash, $buddy)
    {
        $this->gatekeeper->mayI('destroy', 'buddies')->please();
        $user = $this->userRepository->getByHash($userHash);
        // TODO
        // ???
        // PROFIT
    }
} 