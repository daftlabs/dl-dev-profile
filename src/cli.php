<?php
date_default_timezone_set('America/New_York');

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application();

foreach (scandir(__DIR__ . '/commands') as $command) {
    if (substr($command, -4) === '.php' && $command !== 'Command.php') {
        require_once __DIR__ . "/commands/{$command}";
        $filename = substr($command, 0, strlen($command) - 4);
        $commandName = strtolower(implode('-', preg_split('/(?=[A-Z])/', $filename, -1, PREG_SPLIT_NO_EMPTY)));
        $className = "\\Daftswag\\Commands\\{$filename}";
        $application->add(new $className($commandName));
    }
}

$application->run();
