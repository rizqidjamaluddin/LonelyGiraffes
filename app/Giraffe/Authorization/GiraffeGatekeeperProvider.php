<?php  namespace Giraffe\Authorization;

use Giraffe\Common\ConfigurationException;
use Giraffe\Users\UserModel;
use Giraffe\Users\UserRepository;
use stdClass;

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
        return $this->userRepository->get($user);
    }

    public function checkIfUserMay($user, $verb, $noun, $model = null)
    {
        $role = $this->getUserModel($user)->role;
        return $this->resolve($role, $verb, $noun, $model, $this->getUserModel($user));
    }

    public function checkIfGuestMay($verb, $noun, $model = null)
    {
        return $this->resolve('guest', $verb, $noun, $model);
    }

    protected function resolve($role, $verb, $noun, $model = null, $user = null)
    {
        $permissions = $this->loadGroupPermissions();


        // normalize casing
        $noun = strtolower($noun);
        $verb = strtolower($verb);

        // look for user role in permissions lookup
        if (!array_key_exists($role, $permissions)) {
            throw new ConfigurationException("User role '$role' does not exist in permissions table.");
        }

        // look for list of user's "self" permissions
        $selfPermissionList = $permissions[$role]['self'];
        $globalPermissionList = $permissions[$role]['global'];
        if (!(array_key_exists($noun, $selfPermissionList) || array_key_exists($noun, $globalPermissionList))) {
            // noun not found at all, assume access denied
            return false;
        }

        // compile list of permitted verbs for this particular noun
        $selfPermittedVerbs = array_key_exists($noun, $selfPermissionList) ? $selfPermissionList[$noun] : [];
        $globalPermittedVerbs = array_key_exists($noun, $globalPermissionList) ? $globalPermissionList[$noun] : [];

        // it doesn't make any sense to check for a model's permission if the user isn't given as well;
        // default to non-model authorization if user is not provided.
        if ($model && $user) {
            if (!$model instanceof ProtectedResource) {
                throw new ConfigurationException('Model ' . get_class($model) . ' must implement Gatekeeper\ProtectedResource');
            }

            // globally permitted verbs for this noun override self permissions
            if (in_array($verb, $globalPermittedVerbs)) {
                return true;
            }

            $owner = $model->getOwner();
            $ownershipMatch = (integer) $owner->id === (integer) $user->id;
            $permissionMatch = in_array($verb, $selfPermittedVerbs);
            return $ownershipMatch && $permissionMatch;
        } else {
            return in_array($verb, array_merge_recursive($selfPermittedVerbs, $globalPermittedVerbs));
        }
    }

    /**
     * @return array
     */
    protected function loadGroupPermissions()
    {
        $permissions = $this->permissions ? : $this->permissions = $this->permissionsLookup->getGroupPermissions();
        return $permissions;
    }
}