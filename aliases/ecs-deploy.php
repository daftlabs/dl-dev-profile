<?php
date_default_timezone_set('America/New_York');

require_once __DIR__ . '/ecs-db-backup.php';

$AwsGateway = require_once __DIR__ . '/../services/awsGateway.php';

$aws = new AwsGateway(shell_exec('basename `git rev-parse --show-toplevel`'));
$project = $argv[1];
$environment = $argv[2];
$version = $argv[3];
$serviceName = "{$project}-{$environment}";

$service = $aws->findService($serviceName);
$task = $aws->findServiceTask($service);
$taskDefinition = $aws->findTaskDefinition($task);
$newTaskDefinition = buildNewTaskDefinition($taskDefinition, $version);
$newTask = $aws->registerTask($newTaskDefinition['family'], $newTaskDefinition['containerDefinitions']);
$updatedService = $aws->updateService($service, $newTask);
if ($updatedService) {
    echo "Deploying {$updatedService ['taskDefinition']}.\n";
} else {
    echo "Failed to start deploying.\n";
}

function buildNewTaskDefinition(array $oldTask, $newVersion)
{
    $newTask = [
        'family' => $oldTask['family'],
        'containerDefinitions' => $oldTask['containerDefinitions']
    ];
    $url = array_shift(explode(':', $newTask['containerDefinitions'][0]['image']));
    $newTask['containerDefinitions'][0]['image'] = implode(':', [$url, $newVersion]);
    foreach ($newTask['containerDefinitions'][0]['environment'] as &$envVar) {
        if ($envVar['name'] === 'APP_VERSION') {
            $envVar['value'] = $newVersion;
        }
    }
    return $newTask;
}
