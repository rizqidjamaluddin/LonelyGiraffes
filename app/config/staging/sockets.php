<?php

return [
    'port' => 8081,
    'channel' => 'lg-bridge:pipeline-staging',
    'listen' => 'tcp://127.0.0.1:6379',

    'broadcast' => [
        'tcp://172.21.0.10:6379',
        'tcp://172.21.0.11:6379'
    ]
];
