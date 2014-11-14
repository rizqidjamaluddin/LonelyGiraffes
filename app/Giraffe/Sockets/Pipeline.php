<?php  namespace Giraffe\Sockets;

use Giraffe\Common\ConfigurationException;
use Giraffe\Logging\Log;
use Giraffe\Sockets\Broadcasts\Broadcast;
use Giraffe\Sockets\Payload\Payload;
use Illuminate\Redis\Database;
use Predis\Client;

/**
 * Class Pipeline
 *
 * Responsible for managing pub/sub interactions that connect between individual request operations and the socket
 * process.
 *
 * Notes on IDE use: $redis->publish will appear to be a missing method, because it invokes a __call.
 *
 *
*@package Giraffe\Sockets
 */
class Pipeline
{
    /**
     * @var \Predis\Client[]
     */
    protected $servers;

    /**
     * @var bool
     */
    protected $connected = false;

    protected $channel = 'lg-bridge:pipeline';
    /**
     * @var \Giraffe\Logging\Log
     */
    private $log;

    public function __construct(Log $log)
    {
        $this->log = $log;
    }

    /**
     * @param $endpoint
     * @throws ConfigurationException
     */
    public function issue($endpoint)
    {
        $this->assertConnected();
        foreach ($this->servers as $server) {
            $server->publish($this->channel, serialize(new Broadcast($endpoint)));
        }
    }

    /**
     * @param $endpoint
     * @param $payload
     * @throws ConfigurationException
     */
    public function issueWithPayload($endpoint, $payload)
    {
        $this->assertConnected();
        foreach ($this->servers as $server) {
            $server->publish($this->channel, serialize(new Broadcast($endpoint, 'update', new Payload($payload))));
        }

    }

    /**
     * @param Broadcast $broadcast
     * @throws ConfigurationException
     */
    public function dispatch(Broadcast $broadcast)
    {
        $this->assertConnected();
        foreach ($this->servers as $server) {
            $server->publish($this->channel, serialize($broadcast));
        }

    }

    protected function assertConnected()
    {
        if ($this->connected) return;

        $connections = \Config::get('sockets.broadcast');
        if (isset($connections) && count($connections) > 0 ) {
            foreach ($connections as $connection) {
                $this->servers[] = new Client($connection);
            }
        } else {
            throw new ConfigurationException("No broadcast servers defined for sockets (Config sockets.broadcast)");
        }

        $this->connected = true;
    }

    protected function serializeForBridge($data)
    {
        return json_encode($data);
    }
} 