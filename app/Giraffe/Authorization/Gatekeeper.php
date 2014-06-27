<?php  namespace Giraffe\Authorization;

use Auth;
use Dingo\Api\Auth\Shield;
use Giraffe\Common\ConfigurationException;
use Giraffe\Common\NotFoundModelException;
use Giraffe\Logging\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * Class Gatekeeper
 *
 * Basic use:
 * $gatekeeper->iAm($user)->andMayI('edit', 'posts')->please();
 * $gatekeeper->iAm($user)->andMayI('edit', 'post')->for(['post' => $post])->please();
 *
 * Stateful:
 * $gatekeeper->iAm($user);
 * $gatekeeper->mayI('edit', 'posts');
 *
 * Planned (assuming user already set using iAm()):
 * $gatekeeper->put($mod_to_be)->into('moderators')->please();
 * $gatekeeper->remove($inactive_person)->from('moderators')->please();
 * $gatekeeper->allow($some_dude)->to('edit', 'posts')->please();
 * $gatekeeper->ban($poor_guy)->from('edit', 'posts')->please();
 *
 * In the future:
 * $gatekeeper->ban($poor_guy)->from('edit', 'posts')->for('1 month')->and()->removeThem()->from('moderators')->please();
 *
 * Nouns may be plural or singular.
 *
 * Important: Gatekeeper DOES NOT do authentication. It does not check to make sure the user is who they claim to be.
 * It's meant for access control and rights management.
 *
 * @package Giraffe\Helpers\Rights
 */
class Gatekeeper
{

    const REQUEST_NOT_SET = 0;
    const REQUEST_PERMISSION = 1;


    /**
     * @var array
     */
    protected $query = [];

    protected $request = null;

    protected $authenticated = false;
    protected $authenticatedUser;

    /**
     * @var bool
     */
    protected $enable = true;

    /**
     * @var bool
     */
    protected $sudo = false;

    /**
     * @var string
     */
    protected $sudoMessage;

    /**
     * @var GatekeeperProvider
     */
    private $provider;
    /**
     * @var \Giraffe\Logging\Log
     */
    private $log;
    /**
     * @var Auth
     */
    private $auth;

    public function __construct(GatekeeperProvider $gatekeeperProvider, Log $log)
    {
        $this->provider = $gatekeeperProvider;
        $this->request = self::REQUEST_NOT_SET;
        $this->log = $log;
    }

    public function iAm($userIdentifier)
    {

        if (is_null($userIdentifier)) {
            return $this;
        }

        if ($this->enable) {
            try {
                $this->authenticatedUser = $this->provider->getUserModel($userIdentifier);
                if (!$this->authenticatedUser) {
                    // fail on a non-existent/null user
                    return $this;
                }
            } catch (NotFoundModelException $e) {
                return $this;
            }
        }
        $this->authenticated = true;
        return $this;

    }

    public function sudo($message = '')
    {
        $this->sudo = true;
        $this->sudoMessage = $message;
        $this->authenticated = true;
    }

    /**
     * @param $verb string
     * @param $noun string|ProtectedResource
     *
     * @throws \Giraffe\Common\ConfigurationException
     * @return $this
     */
    public function mayI($verb, $noun)
    {
        $this->request = self::REQUEST_PERMISSION;
        $this->query['verb'] = $verb;
        if (is_string($noun)) {
            $this->query['noun'] = Str::singular($noun);
        } else {
            if ($noun instanceof ProtectedResource) {
                $this->query['noun'] = $noun->getResourceName();
                $this->query['model'] = $noun;
            } else {
                throw new ConfigurationException(
                    'Gatekeeper cannot check for permissions on ' . get_class($noun) .
                    '. Please implement ProtectedResource on this model.'
                );
            }
        }
        return $this;
    }

    public function andMayI($verb, $noun)
    {
        return $this->mayI($verb, $noun);
    }

    public function forThis(ProtectedResource $model)
    {
        $this->query['model'] = $model;
        return $this;
    }

    /**
     * Execute command and clear the query details.
     */
    public function please()
    {
        if (!$this->canI()) {
            if ($this->authenticated) {
                throw new GatekeeperException;
            } else {
                throw new GatekeeperUnauthorizedException;
            }
        }
        return true;
    }

    public function canI()
    {
        return $this->resolve();
    }

    // -- Resolving requests --

    protected function resolve()
    {
        $result = null;

        if ($this->sudo) {
            $this->log->notice($this, "Superuser invoked access", ['query' => $this->query]);
        } else {
            switch ($this->request) {
                case self::REQUEST_PERMISSION :
                {
                    if (!$this->enable) {
                        $result = true;
                        break;
                    }
                    $result = $this->resolveRequestPermission();
                    break;
                }
            }
        }


        $this->log->debug($this, "Attempted resource access", ['query' => $this->query, 'result' => $result]);

        $this->reset();
        return $result;
    }

    /**
     * @return bool
     */
    protected function resolveRequestPermission()
    {
        $this->iAmImplicit();
        if ($this->authenticated) {
            if (array_key_exists('model', $this->query)) {
                return $this->provider->checkIfUserMay(
                    $this->authenticatedUser,
                    $this->query['verb'],
                    $this->query['noun'],
                    $this->query['model']
                );
            } else {
                return $this->provider->checkIfUserMay(
                    $this->authenticatedUser,
                    $this->query['verb'],
                    $this->query['noun']
                );
            }
        } else {
            if (array_key_exists('model', $this->query)) {
                return $this->provider->checkIfGuestMay(
                    $this->query['verb'],
                    $this->query['noun'],
                    $this->query['model']
                );
            } else {
                return $this->provider->checkIfGuestMay($this->query['verb'], $this->query['noun']);
            }
        }
    }

    protected function reset()
    {
        $this->request = self::REQUEST_NOT_SET;
        $this->query = Array();
        $this->sudo = false;
    }


    // -- Utilities --

    public function fetchMyModel()
    {
        $this->iAmImplicit();
        return $this->authenticatedUser;
    }

    public function me()
    {
        return $this->fetchMyModel();
    }

    /**
     * Disarm access control features. User given all permissions.
     *
     * Do NOT use this in production code. Intended for testing only.
     *
     * @return $this
     */
    public function disarm()
    {
        $this->enable = false;
        return $this;
    }

    public function isDisarmed()
    {
        return !$this->enable;
    }

    public function why()
    {
        return $this->provider->getLastActionReport();
    }

    /**
     * Implicit version of iAm(), attempts to do Auth::user when it's not given directly.
     * Currently needs to force Dingo/Api to invoke authentication.
     *
     * In an exported package, this mechanism would probably be delegated to a configuration file.
     *
     * @see https://github.com/dingo/api/issues/92
     */
    protected function iAmImplicit()
    {
        // silence exceptions. If shield authentication fails, simply do nothing.
        // for instance, a test may fail because Route::current() is null (since it's being called from a test).
        try {
            /** @var Shield $shield */
            $shield = \App::make('Dingo\Api\Auth\Shield');
            $shield->authenticate(\Request::instance(), \Route::current());
        } catch (\Exception $e) {
        }

        if (!$this->authenticatedUser) {
            $this->iAm(Auth::user());
        }
    }

}