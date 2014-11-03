<?php  namespace Giraffe\Sockets; 
use Giraffe\Users\UserModel;
use Illuminate\Support\Str;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampConnection;
use Ratchet\Wamp\ServerProtocol as WAMP;

class AuthenticatedWampConnection extends WampConnection implements ConnectionInterface
{

    /**
     * @var bool|UserModel
     */
    protected $authentication;

    public function __construct(ConnectionInterface $conn)
    {
        $this->wrappedConn = $conn;

        $this->WAMP            = new \StdClass;
        $this->WAMP->sessionId = Str::random();
        $this->WAMP->prefixes  = array();

        $this->authentication = false;

        $this->send(json_encode(array(WAMP::MSG_WELCOME, $this->WAMP->sessionId, 1, "LonelyGiraffesAPI/2.0.0")));
    }

    public function setAuthentication(UserModel $user)
    {
        $this->authentication = $user;
    }

    public function getAuthentication()
    {
        return $this->authentication;
    }

    public function clearAuthentication()
    {
        $this->authentication = false;
    }
} 