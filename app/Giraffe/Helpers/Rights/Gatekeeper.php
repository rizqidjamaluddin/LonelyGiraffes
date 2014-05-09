<?php  namespace Giraffe\Helpers\Rights;

/**
 * Class Gatekeeper
 *
 * Basic use:
 * $gatekeeper->iAm($user)->andMayI('edit', 'posts')->please();
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

    const REQUEST_NOT_SET    = 0;
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
     * @var GatekeeperProvider
     */
    private $provider;

    public function __construct(GatekeeperProvider $gatekeeperProvider)
    {
        $this->provider = $gatekeeperProvider;
        $this->request = self::REQUEST_NOT_SET;
    }

    public function iAm($userIdentifier)
    {
        $this->authenticatedUser = $this->provider->getUserModel($userIdentifier);
        $this->authenticated = true;
        return $this;
    }




    public function mayI($verb, $noun)
    {
        $this->request = self::REQUEST_PERMISSION;
        $this->query['verb'] = $verb;
        $this->query['noun'] = $noun;
        return $this;
    }

    public function andMayI($verb, $noun)
    {
        return $this->mayI($verb, $noun);
    }

    /**
     * Execute command and clear the query details.
     */
    public function please()
    {
        return $this->resolve();
    }

    // -- Resolving requests --

    protected function resolve()
    {
        $result = null;

        switch ($this->request) {
            case self::REQUEST_PERMISSION : {
                $result = $this->resolveRequestPermission();
                if (!$this->enable) $result = true;
                break;
            }
        }

        $this->reset();
        return $result;
    }

    /**
     * @return bool
     */
    protected function resolveRequestPermission()
    {
        return $this->provider->checkIfUserMay($this->authenticatedUser, $this->query['verb'], $this->query['noun']);
    }

    protected function reset()
    {
        $this->request = self::REQUEST_NOT_SET;
        $this->query = Array();
    }


    // -- Utilities --

    public function fetchMyModel()
    {
        return $this->authenticatedUser;
    }

    public function disarm()
    {
        $this->enable = false;
    }

}