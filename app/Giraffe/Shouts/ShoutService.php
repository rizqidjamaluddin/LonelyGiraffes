<?php  namespace Giraffe\Shouts;

use Giraffe\Authorization\Gatekeeper;
use Giraffe\Common\Service;
use Giraffe\Shouts\ShoutRepository;
use Giraffe\Users\UserRepository;

class ShoutService extends Service
{

    /**
     * @var \Giraffe\Users\UserRepository
     */
    private $userRepository;
    /**
     * @var \Giraffe\Shouts\ShoutRepository
     */
    private $shoutRepository;

    public function __construct(UserRepository $userRepository, ShoutRepository $shoutRepository)
    {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->shoutRepository = $shoutRepository;
    }

    public function create($user, $body)
    {
        $this->gatekeeper->mayI('create', 'shout')->please();
    }
} 