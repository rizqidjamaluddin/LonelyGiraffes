<?php  namespace Giraffe\Common;
use Exception;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Uses 724 This Line Should Be Unreachable
 *
 * Leaving it as an easter egg if anyone finds an unimplemented feature.
 */
class NotImplementedException extends \Exception implements HttpExceptionInterface
{

    public function __construct()
    {
        parent::__construct('This line should be unreachable');
    }


    public function getStatusCode()
    {
        return 724;
    }

    public function getHeaders()
    {
        return [];
    }
}