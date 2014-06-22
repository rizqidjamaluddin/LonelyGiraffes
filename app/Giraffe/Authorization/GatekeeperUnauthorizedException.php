<?php  namespace Giraffe\Authorization; 

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class GatekeeperUnauthorizedException extends UnauthorizedHttpException
{
    public function __construct()
    {
        return parent::__construct('OAuth realm="http://api.lonelygiraffes.com/"');
    }
} 