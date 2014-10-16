<?php

use Giraffe\Stickies\StickyService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class RunSocketServer extends Command {
    protected $name = 'lg:socket:run';
    protected $description = "Run websocket server.";

    public function __construct()
    {
        parent::__construct();
    }

    public function fire()
    {
        $this->info('Starting up websocket server.');

        $loop   = React\EventLoop\Factory::create();

        $webSock = new React\Socket\Server($loop);
        $webSock->listen(8080, '0.0.0.0');
        $webServer = new Ratchet\Server\IoServer(
            new \Ratchet\Http\HttpServer(
                new Ratchet\WebSocket\WsServer(
                    new Ratchet\Wamp\WampServer(
                        new \Giraffe\Sockets\Server()
                    )
                )
            ),
            $webSock
        );
        try {

            $loop->run();
        } catch (Exception $e) {
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