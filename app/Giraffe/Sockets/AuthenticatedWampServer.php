<?php  namespace Giraffe\Sockets; 
use Exception;
use Ratchet\Wamp\JsonException;
use Ratchet\Wamp\ServerProtocol;
use Ratchet\Wamp\TopicManager;
use Ratchet\Wamp\WampServer;
use Ratchet\Wamp\WampServerInterface;
use Ratchet\ConnectionInterface;

class AuthenticatedWampServer extends WampServer
{

    protected $wampProtocol;

    public function __construct(WampServerInterface $app) {
        $this->wampProtocol = new AuthenticatedServerProtocol(new AuthenticatedTopicManager($app));
    }

    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $conn) {
        $this->wampProtocol->onOpen($conn);
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $conn, $msg) {
        try {
            $this->wampProtocol->onMessage($conn, $msg);
        } catch (Exception $we) {
            dd($we->getMessage());
            $conn->close(1007);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $conn) {
        $this->wampProtocol->onClose($conn);
    }

    /**
     * {@inheritdoc}
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        $this->wampProtocol->onError($conn, $e);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubProtocols() {
        return $this->wampProtocol->getSubProtocols();
    }

} 