<?php  namespace Giraffe\Helpers\Rights;

interface GatekeeperProvider
{
    public function getUserModel($user);
    public function checkIfUserMay($user, $verb, $noun);
} 