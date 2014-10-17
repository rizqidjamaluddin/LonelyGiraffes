<?php  namespace Giraffe\Sockets;

use Illuminate\Redis\Database;

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
     * @var \Predis\Client
     */
    protected $redis;

    protected $channel = 'pipeline';

    public function __construct(Database $d)
    {
        $this->redis = $d->connection();
    }

    public function issue($endpoint)
    {
        $this->redis->publish($this->channel, json_encode(['endpoint' => $endpoint]));
    }
} 