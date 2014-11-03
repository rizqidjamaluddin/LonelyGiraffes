<?php  namespace Giraffe\Sockets\Response;

class SocketErrorResponse extends SocketResponse
{
    /**
     * @var string
     */
    protected $identifier;

    public function __construct($payload, $identifier = 'error')
    {
        parent::__construct($payload);
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }
} 