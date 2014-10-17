<?php  namespace Giraffe\Sockets;

use Illuminate\Redis\Database;

/**
 * Class Pipeline
 *
 * Responsible for managing pub/sub interactions that connect between individual request operations and the socket
 * process.
 *
 * @package Giraffe\Sockets
 */
class Pipeline
{
    /**
     * @var \Predis\Client
     */
    protected $redis;

    public function __construct(Database $d)
    {
        $this->redis = $d->connection();
    }

    public function issue($endpoint)
    {
        $this->redis->publish();
    }
} 