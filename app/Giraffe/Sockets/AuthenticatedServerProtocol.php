<?php  namespace Giraffe\Sockets; 

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\ServerProtocol;
use Ratchet\Wamp\WampConnection;

class AuthenticatedServerProtocol extends ServerProtocol
{
    public function onOpen(ConnectionInterface $conn)
    {
        $decor = new AuthenticatedWampConnection($conn);
        $this->connections->attach($conn, $decor);

        $this->_decorating->onOpen($decor);
    }

} 