<?php  namespace Giraffe\Sockets;

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

    protected $channel = 'lg-bridge:pipeline';

    public function __construct(Database $d)
    {
        $connections = \Config::get('sockets.broadcast');
        if (isset($connections) && count($connections) > 0 ) {
            foreach ($connections as $connection) {
                $this->servers[] = new Client($connection);
            }
        } else {
            $this->servers = $d->connection();
        }
    }

    public function issue($endpoint)
    {
        foreach ($this->servers as $server) {
            $server->publish($this->channel, json_encode(['endpoint' => $endpoint]));
        }
    }

    public function serializeForBridge($data)
    {
        return json_encode($data);
    }
} 