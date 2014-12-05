<?php

return [
    'port' => 8080,
    'channel' => 'lg-bridge:pipeline',
    'listen' => 'tcp://127.0.0.1:6379',

    'broadcast' => [
        'tcp://127.0.0.1:6379'
    ]
];