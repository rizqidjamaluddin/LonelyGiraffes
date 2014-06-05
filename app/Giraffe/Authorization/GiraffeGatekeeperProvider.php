<?php  namespace Giraffe\Authorization;

use Giraffe\Users\UserRepository;

class GiraffeGatekeeperProvider implements GatekeeperProvider
{
    /**
     * @var \Giraffe\Users\UserModel
     */
    protected $userModel;

    /**
     * @var \Giraffe\Users\UserRepository
     */
    private $userRepository;
    /**
     * @var GiraffePermissionsLookup
     */
    private $permissionsLookup;

    /**
     * @param UserRepository           $userRepository
     * @param GiraffePermissionsLookup $permissionsLookup
     */
    public function __construct(UserRepository $userRepository, GiraffePermissionsLookup $permissionsLookup)
    {
        $this->userRepository = $userRepository;
        $this->permissionsLookup = $permissionsLookup;
    }

    public function getUserModel($user)
    {
        return $this->userModel ?: $this->userModel = $this->userRepository->get($user);
    }

    public function checkIfUserMay($user, $verb, $noun, $model = null)
    {
        $this->getUserModel($user);
        return false;
    }

    public function checkIfGuestMay($verb, $noun, $model = null)
    {
        return false;
    }
}