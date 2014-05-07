<?php  namespace Giraffe\Helpers\Rights;

use Giraffe\Repositories\UserRepository;

class GiraffeGatekeeperProvider implements GatekeeperProvider
{

    /**
     * @var \Giraffe\Repositories\UserRepository
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

    }
}