<?php  namespace Giraffe\Authorization;

use Giraffe\Users\UserRepository;

class GiraffeGatekeeperProvider implements GatekeeperProvider
{

    /**
     * @var \Giraffe\Users\UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getUserModel($user)
    {
        return $this->userRepository->get($user);
    }

    public function checkIfUserMay($user, $verb, $noun)
    {
        return false;
    }

    public function checkIfGuestMay($verb, $noun)
    {
        // TODO: Implement checkIfGuestMay() method.
        return false;
    }
}