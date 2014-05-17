<?php  namespace Giraffe\Services;

use Giraffe\Helpers\Rights\Gatekeeper;
use Giraffe\Repositories\ShoutRepository;
use Giraffe\Repositories\UserRepository;

class ShoutService extends Service
{

    /**
     * @var \Giraffe\Repositories\UserRepository
     */
    private $userRepository;
    /**
     * @var \Giraffe\Repositories\ShoutRepository
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