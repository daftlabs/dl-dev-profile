<?php
namespace Daftswag\Services;

use Aws\Ecs\EcsClient;
use Daftswag\Helpers\Config;

class EcsGateway
{
    private $client;

    public function __construct($profile)
    {
        $config = new Config($profile);
        $this->client = new EcsClient([
            'version' => 'latest',
            'region' => 'us-east-1',
            'credentials' => [
                'key' => $config->get(Config::AWS_ID),
                'secret' => $config->get(Config::AWS_KEY),
            ]
        ]);
    }

    public function findService($serviceName)
    {
        foreach ($this->listClusters() as $clusterArn) {
            $args = ['cluster' => $clusterArn, 'services' => [$serviceName]];
            if ($service = array_shift($this->client->describeServices($args)->get('services'))) {
                unset($service['events']);
                return $service;
            }
        }

        return null;
    }

    public function getInstanceIdsByService($service)
    {
        $args = ['cluster' => $service['clusterArn'], 'service-name' => $service['serviceName']];
        $taskArns = $this->client->listTasks($args)->get('taskArns');
        $task = array_shift($this->client->describeTasks(['tasks' => $taskArns])->get('tasks'));
        $containers = $this->client
            ->describeContainerInstances(['containerInstances' => [$task['containerInstanceArn']]])
            ->get('containerInstances');
        $instanceIds = array_map(function ($container) {
            return $container['ec2InstanceId'];
        }, $containers);
        return $instanceIds;
    }

    public function findServiceTaskDefinition($service)
    {
        return $this->client
            ->describeTaskDefinition(['taskDefinition' => $service['taskDefinition']])
            ->get('taskDefinition');
    }

    private function listClusters()
    {
        return $this->client->listClusters()->get('clusterArns');
    }
}
