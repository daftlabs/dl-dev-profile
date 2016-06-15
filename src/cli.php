<?php

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application();

foreach (scandir(__DIR__ . '/commands') as $command) {
    if (substr($command, -4) === '.php' && $command !== 'Command.php') {
        require_once __DIR__ . "/commands/{$command}";
        $className = '\\Daftswag\\Commands\\' . substr($command, 0, strlen($command) - 4);
        $application->add(new $className());
    }
}

$application->run();
