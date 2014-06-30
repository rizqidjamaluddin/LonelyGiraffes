<?php  namespace Giraffe\Sessions;

use Giraffe\Common\Service;
use Giraffe\Users\UserRepository;

/**
 * Additional tools that apply on top of the OAuth 2.0 package provided by phpleague.
 *
 * The bridge package set up for Laravel compatibility includes some migrations that implement the interfaces requested
 * by the league package. We're allowed to tweak these tables to our liking.
 *
 * @unstable
 */
class SessionService extends Service
{
    /**
     * @var \Giraffe\Users\UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        parent::__construct();
    }

    public function listSessionsForUser($user)
    {
        $this->userRepository->getByHash($user);
        $this->gatekeeper->mayI('list', 'sessions')->please();

    }

    public function invalidateSession($accessToken)
    {
        $this->gatekeeper->mayI('delete', 'sessions')->please();
    }
} 