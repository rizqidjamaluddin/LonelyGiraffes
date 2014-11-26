<?php

use Monolog\Logger;

return array(

    /**
     * Return a Monolog logger here to configure how log events should be handled.
     */
    'logger' => function() {
        $logger = new Logger('lg');
        $logger->pushHandler(new \Giraffe\Logging\TestReportHandler(storage_path() . '/test-report.log'));
        return $logger;
    }

);