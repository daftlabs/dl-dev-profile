<?php

require __DIR__ . '/../vendor/autoload.php';

use Daftswag\Commands\Greet;
use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new Greet());

$application->run();
