<?php

return [
    'channel' => 'lg-bridge:pipeline-dev',
    'listen' => 'tcp://127.0.0.1:6379',

    'broadcast' => [
        'tcp://172.21.0.10:6379',
        'tcp://172.21.0.11:6379'
    ]
];
