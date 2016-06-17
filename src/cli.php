<?php
date_default_timezone_set('America/New_York');

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application();

foreach (scandir(__DIR__ . '/commands') as $command) {
    if (substr($command, -4) === '.php') {
        $filename = substr($command, 0, strlen($command) - 4);
        $className = "\\Daftswag\\Commands\\{$filename}";
        if ((new ReflectionClass($className))->isAbstract()) {
            continue;
        }
        $commandName = strtolower(implode('-', preg_split('/(?=[A-Z])/', $filename, -1, PREG_SPLIT_NO_EMPTY)));
        $application->add(new $className($commandName));
    }
}

$application->run();
