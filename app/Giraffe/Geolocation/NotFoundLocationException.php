<?php  namespace Giraffe\Geolocation;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotFoundLocationException extends NotFoundHttpException
{
    /**
     * @var array
     */
    private $hints;

    public function __construct(array $hints = [])
    {
        $this->hints = $hints;
    }

    public function getHints()
    {
        return $this->hints;
    }
} 