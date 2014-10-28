<?php namespace Giraffe\Sockets;

use Illuminate\Console\Command;
use Predis\Async\Client;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\ServerProtocol;
use Ratchet\Wamp\Topic;
use Ratchet\Wamp\WampConnection;
use Ratchet\Wamp\WampServerInterface;

class Server implements WampServerInterface
{

    protected $memoryAlert = 20000000;
    protected $highMemory = false;

    /**
     * @var Command
     */
    protected $display;

    /**
     * @var WampConnection
     */
    protected $connection;

    /**
     * @var Topic[]
     */
    protected $subscribedTopics = [];

    public function setDisplay(Command $command)
    {
        $this->display = $command;
    }

    protected function displayInfo($info)
    {
        $this->display->info($info);
    }

    protected function displayLine($output)
    {
        $this->display->getOutput()->writeln($output);
    }

    public function displayOutput($output)
    {
        $this->display->getOutput()->write($output);
    }

    /**
     * Attach redis async instance and begin listening.
     */
    public function attachRedis(Client $client)
    {
        $this->displayOutput(
            'Connecting to redis ... ' . \Config::get('sockets.listen', 'tcp://127.0.0.1:6379') . ' ... '
        );
        $client->pubsub('lg-bridge:pipeline', [$this, 'handleBridgeMessage']);
        $this->displayOutput("<fg=magenta> established.</fg=magenta>\n");
    }

    public function handleBridgeMessage($event)
    {
        $kind = $event->kind;
        $channel = $event->channel;
        $payload = json_decode($event->payload);

        $topic = $payload->endpoint;
        $this->displayLine('Bridge message accepted on $topic');

        if (array_key_exists($topic, $this->subscribedTopics)) {
            $this->displayLine('Broadcasting: ' . $topic);
            $this->subscribedTopics[$topic]->broadcast($event->payload);
        }

    }

    /**
     * When a new connection is opened it will be passed to this method
     *
     * @var Topic                                 $foo
     *
     * @param  ConnectionInterface|WampConnection $conn The socket/connection that just connected to your application
     *
     * @throws \Exception
     */
    function onOpen(ConnectionInterface $conn)
    {
        $this->displayOutput('Client connecting ... Assigning ' . $this->getDisplayPrefix($conn) . "Connected.\n");
    }

    /**
     * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not
     * result in an error if it has already been closed.
     *
     * @param  ConnectionInterface|WampConnection $conn The socket/connection that is closing/closed
     *
     * @throws \Exception
     */
    function onClose(ConnectionInterface $conn)
    {
        $this->displayInfo($this->getDisplayPrefix($conn) . "<fg=red>Connection closed</fg=red>.");
    }

    /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
     * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through
     * this method
     *
     * @param  ConnectionInterface|WampConnection $conn
     * @param  \Exception                         $e
     *
     * @throws \Exception
     */
    function onError(ConnectionInterface $conn, \Exception $e)
    {
        // TODO: Implement onError() method.
    }

    /**
     * An RPC call has been received
     *
     * @param ConnectionInterface|WampConnection $conn
     * @param string                             $id     The unique ID of the RPC, required to respond to
     * @param string|Topic                       $topic  The topic to execute the call against
     * @param array                              $params Call parameters received from the client
     *
     * @return WampConnection
     */
    function onCall(ConnectionInterface $conn, $id, $topic, array $params)
    {
        $this->displayInfo($this->getDisplayPrefix($conn) . "Remote call: <options=bold>$topic</options=bold>");
        return $conn->callResult($id, ['topic' => (string)$topic, 'request_id' => $id]);
    }

    /**
     * A request to subscribe to a topic has been made
     *
     * @param ConnectionInterface|WampConnection $conn
     * @param string|Topic                       $topic The topic to subscribe to
     *
     * @return $this|\Ratchet\ConnectionInterface
     */
    function onSubscribe(ConnectionInterface $conn, $topic)
    {
        // TODO: Implement onSubscribe() method.
        $this->connection = $conn;
        $this->displayOutput($this->getDisplayPrefix($conn) . "Subscribing to $topic ... ");
        if (!array_key_exists($topic->getId(), $this->subscribedTopics)) {
            $this->displayOutput(
                "\n<fg=cyan>Setting up new topic</fg=cyan> → <options=bold>" . (string)$topic . "</options=bold> ... "
            );
            $this->subscribedTopics[$topic->getId()] = $topic;
            $this->displayOutput("OK.\n");
            $this->displayOutput($this->getDisplayPrefix($conn) . "subscribed.\n");
        } else {
            $this->displayOutput("subscribed.\n");
        }
        return $conn->send(json_encode(['response' => 'Subscribed!']));
    }

    /**
     * A request to unsubscribe from a topic has been made
     *
     * @param ConnectionInterface|WampConnection $conn
     * @param string|Topic                       $topic The topic to unsubscribe from
     */
    function onUnSubscribe(ConnectionInterface $conn, $topic)
    {
    }

    /**
     * A client is attempting to publish content to a subscribed connections on a URI
     *
     * @param ConnectionInterface|WampConnection $conn
     * @param string|Topic                       $topic    The topic the user has attempted to publish to
     * @param string                             $event    Payload of the publish
     * @param array                              $exclude  A list of session IDs the message should be excluded from
     *                                                     (blacklist)
     * @param array                              $eligible A list of session Ids the message should be send to
     *                                                     (whitelist)
     */
    function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible)
    {
        // TODO: Implement onPublish() method.
        $this->displayLine($this->getDisplayPrefix($conn) . "<fg=red>Attempted illegal publish.</fg=red>");
    }

    public function handleHeartbeat()
    {
        $memory = memory_get_usage();

        $this->displayOutput(date('Y-m-d H:i:s') . ' | ');
        $this->displayOutput($this->formatBytes($memory) . ' | ');
        $this->displayOutput(count($this->subscribedTopics) . ' Topics');

        $this->displayOutput("\n");

        // memory warning
        if ($this->highMemory) {
            if ($memory < $this->memoryAlert) {
                $this->highMemory = false;
            }
        } else {
            if ($memory > $this->memoryAlert) {
                $this->highMemory = true;
                $this->displayLine("<fg=red>Warning: Memory use above safe limit ({$memory})!</fg=red>");
            }
        }
    }

    protected function formatBytes($bytes, $precision = 3)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * @param ConnectionInterface|WampConnection $conn
     *
     * @return string
     */
    protected function getDisplayPrefix(ConnectionInterface $conn)
    {
        return "<fg=blue>#{$conn->WAMP->sessionId}</fg=blue> <fg=black>→</fg=black> ";
    }

    protected function escape($output)
    {
        return preg_replace('/[^a-zA-Z0-9\/\?]/', '', $output);
    }
}