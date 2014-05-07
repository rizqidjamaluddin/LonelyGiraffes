<?php  namespace Giraffe\Helpers\Rights;

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
     * @param $user stdClass
     * @param $verb string
     * @param $noun string
     *
     * @return bool
     */
    public function checkIfUserMay($user, $verb, $noun);
} 