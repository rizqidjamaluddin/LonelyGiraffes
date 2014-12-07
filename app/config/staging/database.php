<?php

return array(

    'default' => 'mysql',

    'connections' => array(
        'mysql' => array(
            'driver'    => 'mysql',
            'host'      => '172.21.0.10',
            'database'  => 'lgv2_migration',
            'username'  => 'lonelygiraffes',
            'password'  => 'password',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ),
        'v1_mysql' => array(
            'driver'    => 'mysql',
            'host'      => '172.21.0.10',
            'database'  => 'lg_v1',
            'username'  => 'lonelygiraffes',
            'password'  => 'password',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ),
    ),

    'redis' => array(
        'cluster' => false,
        'default' => array(
            'host'     => '172.21.0.10',
            'port'     => 6379,
            'database' => 0,
        ),
    ),

);