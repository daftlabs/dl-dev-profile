<?php

$AwsGateway = require_once __DIR__ . '/../services/awsGateway.php';

$aws = new AwsGateway();
$project = $argv[1];
$environment = $argv[2];
$cmd = implode(' ', array_slice($argv, 3));
$serviceName = "{$project}-{$environment}";

$service = $aws->findService($serviceName);
$task = $aws->findServiceTask($service);
$hosts = $aws->findContainerHosts($task);

foreach ($hosts as $host) {
    foreach (explode("\n", $aws->runCmdInEC2Instance($host, 'docker ps')) as $containerDescription) {
        if (stristr($containerDescription, $serviceName)) {
            $description = preg_split('/\s{2,}/', $containerDescription);
            $res = $aws->runCmdInEC2Instance($host, "docker exec -i {$description[0]} $cmd");
            echo implode("\n", ['======', $host, '======', $res]) . "\n";
            break;
        }
    }
}
