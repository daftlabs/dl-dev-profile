<?php
namespace Daftswag\Services;

use Aws\Ecs\EcsClient;

class EcsGateway extends AwsGateway
{
    private $client;

    public function __construct($profile)
    {
        parent::__construct($profile);
        $this->client = new EcsClient($this->getAwsConfig());
    }

    public function findService($serviceName)
    {
        foreach ($this->listClusters() as $clusterArn) {
            $args = ['cluster' => $clusterArn, 'services' => [$serviceName]];
            $service = array_shift($this->client->describeServices($args)->get('services'));
            if ($service && $service['status'] == 'ACTIVE') {
                unset($service['events']);
                return $service;
            }
        }

        return null;
    }

    public function getInstanceIdsByService($service)
    {
        $args = ['cluster' => $service['clusterArn'], 'serviceName' => $service['serviceName']];
        $taskArns = $this->client->listTasks($args)->get('taskArns');
        $task = array_shift($this->client->describeTasks(['cluster' => $service['clusterArn'], 'tasks' => $taskArns])->get('tasks'));
        $containers = $this->client
            ->describeContainerInstances(['cluster' => $service['clusterArn'], 'containerInstances' => [$task['containerInstanceArn']]])
            ->get('containerInstances');
        $instanceIds = array_map(function ($container) {
            return $container['ec2InstanceId'];
        }, $containers);


        return $instanceIds;
    }

    public function findServiceTaskDefinition($service)
    {
        return $this->client
            ->describeTaskDefinition(['cluster' => $service['clusterArn'], 'taskDefinition' => $service['taskDefinition']])
            ->get('taskDefinition');
    }

    private function listClusters()
    {
        return $this->client->listClusters()->get('clusterArns');
    }

    public function registerTask($family, array $containerDefinitions)
    {
        return $this->client->registerTaskDefinition([
            'family' => $family,
            'containerDefinitions' => $containerDefinitions,
        ])->get('taskDefinition');
    }

    public function updateService(array $service, array $task)
    {
        return $this->client->updateService([
            'cluster' => $service['clusterArn'],
            'service' => $service['serviceName'],
            'taskDefinition' => $task['taskDefinitionArn'],
        ])->get('service');
    }
}
