<?php  namespace Giraffe\Sockets; 
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\TopicManager;
use Ratchet\Wamp\WampConnection;

class AuthenticatedTopicManager extends TopicManager
{
    /**
     * @param WampConnection        $conn
     * @param \Ratchet\Wamp\Topic|string $topic
     */
    public function onSubscribe(ConnectionInterface $conn, $topic)
    {
        try {
            $topicObj = $this->getTopic($topic);
        } catch (InvalidEndpointException $e) {
            $conn->send("Invalid endpoint given.");
            return;
        }

        if ($conn->WAMP->subscriptions->contains($topicObj)) {
            return;
        }

        $this->topicLookup[$topic]->add($conn);
        $conn->WAMP->subscriptions->attach($topicObj);
        $this->app->onSubscribe($conn, $topicObj);
    }

    public function onUnsubscribe(ConnectionInterface $conn, $topic)
    {
        $topicObj = $this->getTopic($topic);

        if (!$conn->WAMP->subscriptions->contains($topicObj)) {
            return;
        }

        $this->cleanTopic($topicObj, $conn);

        $this->app->onUnsubscribe($conn, $topicObj);
    }


    /**
     * Convert from a JSON Topic to a regular topic, with the endpoint as the topic ID.
     *
     * @param $topic
     * @throws InvalidEndpointException
     * @return \Ratchet\Wamp\Topic
     */
    protected function getTopic($topic)
    {
//        $decoded = json_decode($topic);
//        if (!isset($decoded->endpoint)) {
//            throw new InvalidEndpointException;
//        }
        return parent::getTopic($topic); // TODO: Change the autogenerated stub
    }

} 