<?php

class AwsGateway
{
    public function findService($serviceName)
    {
        $services = [];
        foreach (static::listClusters() as $cluster) {
            $next = static::ecsCmd('describe-services', ['cluster' => $cluster, 'service' => $serviceName]);
            $services = array_merge($services, $next['services']);
        }

        $service = array_shift(array_filter($services, function (array $service) {
            return array_key_exists('status', $service) && $service['status'] === 'ACTIVE';
        }));

        unset($service['events']);
        return $service;
    }

    public function findServiceTask(array $service)
    {
        $ARNs = $this->ecsCmd('list-tasks', ['cluster' => $service['clusterArn'], 'service-name' => $service['serviceName']]);
        $tasks = $this->ecsCmd('describe-tasks', ['cluster' => $service['clusterArn'], 'tasks' => $ARNs['taskArns']]);
        return array_shift(array_filter($tasks['tasks'], function (array $task) use ($service) {
            return stristr($task['taskDefinitionArn'], $service['serviceName']);
        }));
    }

    private function listClusters()
    {
        return array_filter(array_map([$this, 'nameFromArn'], $this->ecsCmd('list-clusters')['clusterArns']));
    }

    public function findTaskDefinition(array $task)
    {
        return $this->ecsCmd('describe-task-definition', ['task-definition' => $this->nameFromArn($task['taskDefinitionArn'])])['taskDefinition'];
    }

    public function findContainerHosts(array $task)
    {
        $hosts = [];
        $instances = $this->ecsCmd('describe-container-instances', ['container-instances' => $task['containerInstanceArn']]);
        $instanceIds = array_map(function ($instance) {
            return $instance['ec2InstanceId'];
        }, $instances['containerInstances']);
        foreach ($this->ec2Cmd('describe-instances', ['instance-ids' => $instanceIds])['Reservations'] as $reservation) {
            $hosts = array_merge($hosts, array_map(function ($instance) {
                return $instance['PublicIpAddress'];
            }, $reservation['Instances']));
        }
        return $hosts;
    }

    public function runCmdInEC2Instance($host, $cmd)
    {
        return shell_exec("ssh ec2-user@{$host} -i ~/.ssh/ecs.pem '$cmd'");
    }

    private function nameFromArn($arn)
    {
        return end(explode("/", $arn));
    }

    private function ec2Cmd($cmd, array $options = [])
    {
        return static::awsCmd("ec2 {$cmd}", $options);
    }

    private function ecsCmd($cmd, array $options = [])
    {
        return static::awsCmd("ecs {$cmd}", $options);
    }

    private function awsCmd($cmd, array $options = [])
    {
        $options = array_merge($options, ['output' => 'json']);
        foreach ($options as $key => $val) {
            $cmd .= is_array($val) ? " --{$key} " . implode(' ', $val) : " --{$key}={$val}";
        }

        echo "aws {$cmd}\n";
        return json_decode(shell_exec("aws {$cmd}"), true);
    }
}
