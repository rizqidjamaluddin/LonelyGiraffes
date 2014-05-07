<?php  namespace Giraffe\Helpers\Rights;

interface GatekeeperProvider
{
    public function getUserModel($user);
} 