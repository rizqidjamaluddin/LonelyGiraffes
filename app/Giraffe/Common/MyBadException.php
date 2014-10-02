<?php  namespace Giraffe\Common; 

use Symfony\Component\HttpKernel\Exception\HttpException;

class MyBadException extends HttpException
{

    protected $generic = "My bad, something went wrong! We'll have staff look at this, along with our team of ninjas.";

    public function __construct($message = null)
    {
        if (!$message) $message = $this->generic;
        parent::__construct(500, $message);
    }
} 