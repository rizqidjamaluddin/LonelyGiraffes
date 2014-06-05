<?php  namespace Giraffe\Authorization;

use Giraffe\Common\ConfigurationException;
use Giraffe\Users\UserRepository;

class GiraffeGatekeeperProvider implements GatekeeperProvider
{
    /**
     * @var \Giraffe\Users\UserModel
     */
    protected $userModel;

    /**
     * @var array
     */
    protected $permissions;

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
        $role = $this->getUserModel($user)->role;
        return $this->resolve($role, $verb, $noun, $model);
    }

    public function checkIfGuestMay($verb, $noun, $model = null)
    {
        return $this->resolve('guest', $verb, $noun, $model);
    }

    protected function resolve($role, $verb, $noun, $model = null)
    {
        $permissions = $this->permissions ?: $this->permissions = $this->permissionsLookup->getGroupPermissions();

        if ($model) {

        } else {
            // look for user role in permissions lookup
            if (!array_key_exists($role, $permissions)) {
                throw new ConfigurationException("User role does not exist in permissions table.");
            }

            // look for list of user's "self" permissions
            $permissionList = $permissions[$role]['self'];
            if (!array_key_exists($noun, $permissionList)) {
                // noun not found at all, assume access denied
                return false;
            }

            // look if verb is listed in array of permitted verbs
            return in_array($verb, $permissionList[$noun]);
        }
    }
}