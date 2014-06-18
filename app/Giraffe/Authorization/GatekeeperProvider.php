<?php  namespace Giraffe\Authorization;

use stdClass;

interface GatekeeperProvider
{
    /**
     * Obtain a user model. Should fetch a proper user model from the persistence layer.
     *
     * @param $user mixed Arbitrary representation of a user.
     * @return stdClass
     */
    public function getUserModel($user);

    /**
     * @param \stdClass      $user
     * @param                $verb
     * @param                $noun
     * @param null|\stdClass $model
     *
     * @return bool
     */
    public function checkIfUserMay($user, $verb, $noun, $model = null);

    /**
     * @param           $verb
     * @param           $noun
     * @param \stdClass $model
     *
     * @return mixed
     */
    public function checkIfGuestMay($verb, $noun, $model = null);

    public function getLastActionReport();
} 