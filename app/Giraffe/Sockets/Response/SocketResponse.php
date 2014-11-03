<?php  namespace Giraffe\Sockets\Response; 
class SocketResponse 
{
    /**
     * @var array
     */
    protected $payload;

    public function __construct($payload)
    {
        if (!is_array($payload)) {
            $payload = [$payload];
        }

        $this->payload = $payload;
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }
} 