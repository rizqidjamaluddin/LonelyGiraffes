<?php  namespace Giraffe\Sockets; 
use Illuminate\Support\Str;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampConnection;
use Ratchet\Wamp\ServerProtocol as WAMP;

class AuthenticatedWampConnection extends WampConnection implements ConnectionInterface
{
    public function __construct(ConnectionInterface $conn)
    {
        $this->wrappedConn = $conn;

        $this->WAMP            = new \StdClass;
        $this->WAMP->sessionId = Str::random();
        $this->WAMP->prefixes  = array();

        $this->send(json_encode(array(WAMP::MSG_WELCOME, $this->WAMP->sessionId, 1, "LonelyGiraffes/1.0.0")));
    }
} 