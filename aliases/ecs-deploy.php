<?php
date_default_timezone_set('America/New_York');

$AwsGateway = require(__DIR__ . '/../services/awsGateway.php');

$aws = new AwsGateway();
$project = $argv[1];
$environment = $argv[2];
$version = $argv[3];
$serviceName = "{$project}-{$environment}";

$service = $aws->findService($serviceName);
$task = $aws->findServiceTask($service);
$taskDefinition = $aws->findTaskDefinition($task);
$newTaskDefinition = buildNewTaskDefiniton($taskDefinition, $version);
$newTask = $aws->registerTask($newTaskDefinition['family'], $newTaskDefinition['containerDefinitions']);
print_r($newTask);
die();

function buildNewTaskDefiniton(array $oldTask, $newVersion)
{
    $newTask = [
        'family' => $oldTask['family'],
        'containerDefinitions' => $oldTask['containerDefinitions']
    ];
    foreach ($newTask['containerDefinitions'] as &$containerDefinition) {
        $containerDefinition['image'] = implode(':', [array_shift(explode(':', $containerDefinition['image'])), $newVersion]);
        foreach ($containerDefinition['environment'] as &$envVar) {
            if ($envVar['name'] === 'APP_VERSION') {
                $envVar['value'] = $newVersion;
            }
        }
    }
    return $newTask;
}
