<?php  namespace Giraffe\Sockets\Payload;
class Payload
{

    protected $contents;

    /**
     * @return mixed
     */
    public function getContents()
    {
        return $this->contents;
    }

    public function __construct($contents)
    {
        $this->contents = $contents;
    }

} 