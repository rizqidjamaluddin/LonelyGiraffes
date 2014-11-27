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
        $logger = new Monolog\Logger('lg');

        $graylogHandler = new Monolog\Handler\GelfHandler(new Gelf\Publisher(new Gelf\Transport\UdpTransport("172.21.0.3", 514)), Monolog\Logger::INFO);
        $graylogHandler->setFormatter(new \Monolog\Formatter\GelfMessageFormatter("LGv2-dev"));
        $flowdockHandler = new FlowdockChatHandler('014067cfe257dc4572f903b6b440f7ed', Logger::INFO);
        $flowdockHandler->setFormatter(new \Giraffe\Logging\FlowdockChatFormatter('014067cfe257dc4572f903b6b440f7ed'));
        $essentialHandler = new FingersCrossedHandler(new StreamHandler(storage_path() . '/essential/lg.log'), new ErrorLevelActivationStrategy(Logger::ERROR));

        $group = new GroupHandler([$graylogHandler, $flowdockHandler, $essentialHandler]);

        $logger->pushHandler($group);
        return $logger;
    }

);