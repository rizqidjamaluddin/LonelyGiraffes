<?php

use Gelf\Publisher;
use Gelf\Transport\UdpTransport;
use Giraffe\Logging\FlowdockChatHandler;
use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\GelfHandler;
use Monolog\Handler\GroupHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

return array(

    'logger' => function() {
        $logger = new Logger('lg');

        $graylogHandler = new GelfHandler(new Publisher(new UdpTransport("127.0.0.1", 12201)), Logger::INFO);
        $flowdockHandler = new FlowdockChatHandler('014067cfe257dc4572f903b6b440f7ed', Logger::INFO);
        $essentialHandler = new FingersCrossedHandler(new StreamHandler(storage_path() . '/essential/lg.log'), new ErrorLevelActivationStrategy(Logger::ERROR));

        $group = new GroupHandler([$graylogHandler, $flowdockHandler, $essentialHandler]);

        $logger->pushHandler($group);
        return $logger;
    }

);