<?php namespace Giraffe\Sockets;

use Illuminate\Console\Command;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\ServerProtocol;
use Ratchet\Wamp\Topic;
use Ratchet\Wamp\WampServerInterface;

class Server implements WampServerInterface
{

    /**
     * @var Command
     */
    protected $display;

    private $test;

    public function setDisplay(Command $command){
        $this->display = $command;
    }

    protected function displayInfo($info)
    {
        $this->display->info($info);
    }

    /**
     * Attach redis async instance and begin listening.
     */
    public function attachRedis($client)
    {

    }

    /**
     * When a new connection is opened it will be passed to this method
     *
     * @var Topic $foo
     *
     * @param  ConnectionInterface $conn The socket/connection that just connected to your application
     * @throws \Exception
     */
    function onOpen(ConnectionInterface $conn)
    {
        $this->test = $conn;

        // TODO: Implement onOpen() method.
        echo "opened\n";
//        $conn->send(json_encode(['greeting' => 'Welcome to Lonely Giraffes!']));
        $this->displayInfo('Connection opened.');

    }

    /**
     * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not result in an error if it has already been closed.
     *
     * @param  ConnectionInterface $conn The socket/connection that is closing/closed
     * @throws \Exception
     */
    function onClose(ConnectionInterface $conn)
    {
        // TODO: Implement onClose() method.
        echo "closed \n";
    }

    /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
     * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method
     *
     * @param  ConnectionInterface $conn
     * @param  \Exception          $e
     * @throws \Exception
     */
    function onError(ConnectionInterface $conn, \Exception $e)
    {
        // TODO: Implement onError() method.
    }

    /**
     * An RPC call has been received
     *
     * @param \Ratchet\ConnectionInterface $conn
     * @param string                       $id     The unique ID of the RPC, required to respond to
     * @param string|Topic                 $topic  The topic to execute the call against
     * @param array                        $params Call parameters received from the client
     */
    function onCall(ConnectionInterface $conn, $id, $topic, array $params)
    {
        // TODO: Implement onCall() method.
        var_dump($id);
        var_dump((string)$topic);
        var_dump($params);
        $conn->send(json_encode(['response' => 'Hey!']));
        return $conn->callResult($id, ['topic' => (string) $topic, 'request_id' => $id]);
    }

    /**
     * A request to subscribe to a topic has been made
     *
     * @param \Ratchet\ConnectionInterface $conn
     * @param string|Topic                 $topic The topic to subscribe to
     */
    function onSubscribe(ConnectionInterface $conn, $topic)
    {
        // TODO: Implement onSubscribe() method.

        echo "sub \n";
        echo "Topic: $topic \n";
        return $conn->send(json_encode(['response' => 'Subscribed!']));
    }

    /**
     * A request to unsubscribe from a topic has been made
     *
     * @param \Ratchet\ConnectionInterface $conn
     * @param string|Topic                 $topic The topic to unsubscribe from
     */
    function onUnSubscribe(ConnectionInterface $conn, $topic)
    {
        // TODO: Implement onUnSubscribe() method.
        echo "unsub \n";
    }

    /**
     * A client is attempting to publish content to a subscribed connections on a URI
     *
     * @param \Ratchet\ConnectionInterface $conn
     * @param string|Topic                 $topic    The topic the user has attempted to publish to
     * @param string                       $event    Payload of the publish
     * @param array                        $exclude  A list of session IDs the message should be excluded from (blacklist)
     * @param array                        $eligible A list of session Ids the message should be send to (whitelist)
     */
    function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible)
    {
        // TODO: Implement onPublish() method.
        echo "publish \n";
        var_dump($topic);
        echo "Topic: " . $topic . "\n";
        echo "Event: " . $event . "\n";
}}