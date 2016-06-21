<?php
namespace Daftswag\Services;

use Aws\Ec2\Ec2Client;
use Daftswag\Helpers\Config;
use Exception;

class Ec2Gateway extends AwsGateway
{
    private $client;

    public function __construct($profile)
    {
        parent::__construct($profile);
        $this->client = new Ec2Client($this->getAwsConfig());
    }

    public function getHosts(array $instanceIds)
    {
        $hosts = [];
        $reservations = $this->client->describeInstances(['InstanceIds' => $instanceIds])->get('Reservations');
        foreach ($reservations as $reservation) {
            $hosts = array_merge($hosts, array_map(function ($instance) {
                return $instance['PublicIpAddress'];
            }, $reservation['Instances']));
        }

        return array_filter($hosts);
    }

    public function runCmd($host, $cmd, $pem = '~/.ssh/ecs.pem')
    {
        return shell_exec("ssh ec2-user@{$host} -i {$pem} '{$cmd}'");
    }
}
