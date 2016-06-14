<?php
date_default_timezone_set('America/New_York');

$AwsGateway = require(__DIR__ . '/../services/awsGateway.php');

$aws = new AwsGateway();
$project = $argv[1];
$environment = $argv[2];
$serviceName = "{$project}-{$environment}";

$service = $aws->findService($serviceName);
$task = $aws->findServiceTask($service);
$taskDefinition = $aws->findTaskDefinition($task);
$version = array_pop(explode(':', $taskDefinition['containerDefinitions'][0]['image']));
$backup = "{$serviceName}-{$version}-" . date('Y-m-d-G:i:s') . '.sql';

$env = [];
foreach ($taskDefinition['containerDefinitions'][0]['environment'] as $envVar) {
    $env[$envVar['name']] = $envVar['value'];
}
$db = [
    'user' => arrayPluck($env, ['DB_USER', 'DB_USERNAME', 'MYSQL_USER', 'MYSQL_USERNAME']),
    'pass' => arrayPluck($env, ['DB_PASS', 'DB_PASSWORD', 'MYSQL_PASS', 'MYSQL_PASSWORD']),
    'host' => arrayPluck($env, ['DB_HOST', 'MYSQL_HOST']),
    'name' => arrayPluck($env, ['DB_NAME', 'DB_DATABASE', 'MYSQL_NAME', 'MYSQL_DATABASE']),
];

$exportCmd = implode(' ', [
    'mysqldump',
    "-u {$db['user']}",
    "-p{$db['pass']}",
    "-h {$db['host']}",
    "{$db['name']} > " . __DIR__ . "/../db-backups/{$backup}"
]);

echo $exportCmd . "\n";
echo shell_exec($exportCmd);

function arrayPluck($arr, array $keys = [])
{
    foreach ($keys as $key) {
        if (array_key_exists($key, $arr)) {
            return $arr[$key];
        }
    }
    return null;
}
