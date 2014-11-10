<?php  namespace Giraffe\Sockets\Broadcasts;

use Giraffe\Sockets\Payload\Payload;

class Broadcast
{
    /**
     * @var string
     */
    private $endpoint;

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * @return string
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return Payload
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @var string
     */
    private $event;
    /**
     * @var Payload
     */
    private $payload;

    public function __construct($endpoint, $event = 'update', Payload $payload = null)
    {
        $this->endpoint = $endpoint;
        $this->event = $event;
        $this->payload = $payload;
    }

    public function toJson()
    {
        return json_encode(
            [
                'endpoint' => $this->getEndpoint(),
                'event'    => $this->getEvent(),
                'payload'  => $this->getPayload()->getContents()
            ]
        );
    }
} 