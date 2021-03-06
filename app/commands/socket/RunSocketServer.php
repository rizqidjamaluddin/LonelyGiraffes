<?php

use Giraffe\Stickies\StickyService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class RunSocketServer extends Command
{
    protected $name = 'lg:socket:run';
    protected $description = "Run websocket server.";

    public function __construct()
    {
        parent::__construct();
    }

    public function fire()
    {
        $this->info('Starting up websocket server.');

        $loop = React\EventLoop\Factory::create();
        $server = new \Giraffe\Sockets\Server();
        $server->setDisplay($this);
        $server->setListenChannel(Config::get('sockets.channel', 'lg-bridge:pipeline'));

        // attach memory reminder
        $loop->addPeriodicTimer(10, [$server, 'handleHeartbeat']);

        // attach redis
        $client = new \Predis\Async\Client(Config::get('sockets.listen', 'tcp://127.0.0.1:6379') . "?read_write_timeout=-1", $loop);
        $client->connect([$server, 'attachRedis']);

        $webSock = new React\Socket\Server($loop);
        $webSock->listen(Config::get('sockets.port', 8080), '0.0.0.0');
        $webServer = new Ratchet\Server\IoServer(
            new \Ratchet\Http\HttpServer(
                new Ratchet\WebSocket\WsServer(
                    new \Giraffe\Sockets\AuthenticatedWampServer($server)
                )
            ),
            $webSock
        );
        try {
            $loop->run();
        } catch (Exception $e) {
            var_dump($e->getMessage());
            dd($e->getTraceAsString());
        }
    }


    protected function getArguments()
    {
        return [];
    }

    protected function getOptions()
    {
        return [];
    }

} 