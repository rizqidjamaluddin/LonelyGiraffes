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
     * @param      $user
     * @param      $verb
     * @param      $noun
     * @param null $model
     *
     * @return bool
     */
    public function checkIfUserMay($user, $verb, $noun, $model = null);

    /**
     * @param $verb
     * @param $noun
     *
     * @return mixed
     */
    public function checkIfGuestMay($verb, $noun);
} 