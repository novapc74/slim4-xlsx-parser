#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Monolog\Logger;
use App\Console\ImportCsvAds;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../', '.env');
$dotenv->load();

try {

    /** @var ContainerInterface $container */
    $container = require __DIR__ . '/../src/Config/container.php';

    $cli = new Application('Console');
    (require __DIR__ . '/../src/Config/eloquent_console.php')($cli, $container);

    $logger = $container->get(Logger::class);

    $cli->add(new ImportCsvAds($logger));

    $cli->run();

} catch (Throwable $e) {
    echo $e->getMessage();
}