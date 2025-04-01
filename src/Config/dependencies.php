<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

return [
    'eloquent_loc'=>[
        'driver'    => 'mysql',
        'host'      => $_ENV['DB_HOST'],
        'port'      => $_ENV['DB_PORT'],
        'database'  => $_ENV['DB_NAME'],
        'username'  => $_ENV['DB_USER'],
        'password'  => $_ENV['DB_PASSWORD'],
        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_general_ci',
        'prefix'    => '',
    ],

    Logger::class => function () {
        $logger = new Logger('app');
        $logger->pushHandler(new StreamHandler(__DIR__ . '/../../var/log/app.log', Logger::DEBUG));
        return $logger;
    },
];