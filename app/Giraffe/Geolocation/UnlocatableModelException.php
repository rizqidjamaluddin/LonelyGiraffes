<?php  namespace Giraffe\Geolocation; 
use Symfony\Component\HttpKernel\Exception\PreconditionRequiredHttpException;

class UnlocatableModelException extends PreconditionRequiredHttpException
{
    public function __construct($message = 'No location given')
    {
        return parent::__construct($message);
    }
} 