<?php  namespace Giraffe\Sockets;

class Broadcast
{
    /**
     * Name to show under "event" property sent in JSON.
     *
     * @return string
     */
    public function getName()
    {
        return "broadcast";
    }

    /**
     * JSON payload to send to socket users.
     *
     * @return string
     */
    public function getPayload()
    {
        return "";
    }
} 