<?php  namespace Giraffe\Authorization;

use Giraffe\Common\ConfigurationException;
use Giraffe\Users\UserModel;
use Giraffe\Users\UserRepository;
use stdClass;

class GiraffeGatekeeperProvider implements GatekeeperProvider
{

    protected $lastRequest;
    protected $report;

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

        $this->lastRequest = [];
        $this->lastRequest = ['role' => $role, 'verb' => $verb, 'noun' => $noun];
        if ($model) {
            $this->lastRequest['model'] = get_class($model);
        }
        if ($user) {
            $this->lastRequest['user'] = $user->id;
        }


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
            $this->report = 'Noun not registered in permissions list';
            return false;
        }

        // compile list of permitted verbs for this particular noun
        $selfPermittedVerbs = array_key_exists($noun, $selfPermissionList) ? $selfPermissionList[$noun] : [];
        $globalPermittedVerbs = array_key_exists($noun, $globalPermissionList) ? $globalPermissionList[$noun] : [];

        // it doesn't make any sense to check for a model's permission if the user isn't given as well;
        // default to non-model authorization if user is not provided.
        if ($model && $user) {
            if (!$model instanceof ProtectedResource) {
                throw new ConfigurationException(
                    'Model ' . get_class($model) . ' must implement Gatekeeper\ProtectedResource'
                );
            }

            // globally permitted verbs for this noun override self permissions
            if (in_array($verb, $globalPermittedVerbs)) {
                $this->report = "User permitted global access to $verb $noun";
                return true;
            }

            $ownershipMatch = $model->checkOwnership($user);
            $permissionMatch = in_array($verb, $selfPermittedVerbs);
            if ($ownershipMatch && $permissionMatch) {
                $this->report = 'User access approved for self-owned resource';
                return true;
            } else {
                if (!$permissionMatch) {
                    $this->report = 'User access denied because of insufficient permissions';
                } else {
                    $this->report = 'User access denied because of resource ownership';

                }
                return false;
            }
        } else {
            if (in_array($verb, $selfPermittedVerbs)) {
                $this->report = "User approved to $verb $noun through self permissions";
                return true;
            } else {
                if (in_array($verb, $globalPermittedVerbs)) {
                    $this->report = "User permitted global access to $verb $noun";
                    return true;
                } else {
                    $this->report = "User denied to $verb $noun because of insufficient permissions";
                    return false;
                }
            }
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


    public function getLastActionReport()
    {
        $role = $this->lastRequest['role'];
        $verb = $this->lastRequest['verb'];
        $noun = $this->lastRequest['noun'];
        $user = array_key_exists('user', $this->lastRequest) ? ' ' . $this->lastRequest['user'] . ' : ' : '';
        $model = array_key_exists('model', $this->lastRequest) ? ' on ' . $this->lastRequest['model'] : '';
        return "[$role $user$verb->$noun$model] " . $this->report;
    }
}