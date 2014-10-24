<?php  namespace Giraffe\Sockets;

use Giraffe\Common\ConfigurationException;
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

    public function __construct()
    {
    }

    public function issue($endpoint)
    {
        $this->assertConnected();
        foreach ($this->servers as $server) {
            $server->publish($this->channel, json_encode(['endpoint' => $endpoint]));
        }
    }

    public function issueWithPayload($endpoint, $payload)
    {
        $this->assertConnected();
        foreach ($this->servers as $server) {
            $server->publish($this->channel, json_encode(['endpoint' => $endpoint, 'payload' => json_encode($payload)]));
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

    public function serializeForBridge($data)
    {
        return json_encode($data);
    }
} 