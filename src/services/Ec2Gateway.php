<?php
namespace Daftswag\Services;

use Aws\Ec2\Ec2Client;
use Daftswag\Helpers\Config;

class Ec2Gateway
{
    private $client;

    public function __construct($profile)
    {
        $config = new Config($profile);
        $this->client = new Ec2Client([
            'version' => 'latest',
            'region' => 'us-east-1',
            'credentials' => [
                'key' => $config->get(Config::AWS_ID),
                'secret' => $config->get(Config::AWS_KEY),
            ]
        ]);
    }

    public function getHosts(array $instanceIds)
    {
        $hosts = [];
        $reservations = $this->client->describeInstances(['instanceIds' => $instanceIds])->get('Reservations');
        foreach ($reservations as $reservation) {
            $hosts = array_merge($hosts, array_map(function ($instance) {
                return $instance['PublicIpAddress'];
            }, $reservation['Instances']));
        }
        return array_filter($hosts);
    }
}
