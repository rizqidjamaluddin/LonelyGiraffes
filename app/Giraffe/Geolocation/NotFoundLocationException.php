<?php  namespace Giraffe\Geolocation;

class NotFoundLocationException extends \Exception
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