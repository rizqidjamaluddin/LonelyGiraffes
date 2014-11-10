<?php  namespace Giraffe\Sockets; 
use Giraffe\Sockets\Broadcasts\Broadcast;
use Giraffe\Sockets\Payload\AuthenticatedPayload;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;
use Ratchet\Wamp\WampConnection;

class AuthenticatedTopic extends Topic
{
    public $autoDelete = false;

    protected $id;

    protected $subscribers;

    public function __construct($topicId)
    {
        $this->id = $topicId;
        $this->subscribers = new \SplObjectStorage;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    public function __toString()
    {
        return $this->getId();
    }

    public function broadcast($msg, array $exclude = array(), array $eligible = array())
    {
        if (!($msg instanceof Broadcast)) throw new \Exception('Broadcast payload invalid.');

        $useEligible = (bool) count($eligible);
        $payload = $msg->getPayload();
        $authenticate = $payload instanceof AuthenticatedPayload;

        foreach ($this->subscribers as $client) {
            if (in_array($client->WAMP->sessionId, $exclude)) {
                continue;
            }

            if ($authenticate) {
                if ($client->authentication) {
                    if (!$payload->canAccess($client->authentication)) {
                        continue;
                    }
                } else {
                    continue;
                }
            }

            if ($useEligible && !in_array($client->WAMP->sessionId, $eligible)) {
                continue;
            }

            $client->event($this->id, $msg->toJson());
        }
        unset($msg);

        return $this;
    }

    /**
     * @param  WampConnection $conn
     * @return boolean
     */
    public function has(ConnectionInterface $conn)
    {
        return $this->subscribers->contains($conn);
    }

    /**
     * @param WampConnection $conn
     * @return Topic
     */
    public function add(ConnectionInterface $conn)
    {
        $this->subscribers->attach($conn);

        return $this;
    }

    /**
     * @param WampConnection $conn
     * @return Topic
     */
    public function remove(ConnectionInterface $conn)
    {
        if ($this->subscribers->contains($conn)) {
            $this->subscribers->detach($conn);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return $this->subscribers;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->subscribers->count();
    }
} 