<?php
namespace Daftswag\Services;

use Aws\Ec2\Ec2Client;
use Daftswag\Helpers\Config;
use Exception;

abstract class AwsGateway
{
    protected $config;

    public function __construct($profile)
    {
        $this->config = new Config($profile);
    }

    protected function getAwsConfig()
    {
        $key = $this->config->get(Config::AWS_ID);
        $secret = $this->config->get(Config::AWS_KEY);
        if (!$key || !$secret) {
            throw new Exception ('Missing AWS key or secret; have you run `dl setup`?');
        }
        return [
            'version' => 'latest',
            'region' => 'us-east-1',
            'credentials' => [
                'key' => $key,
                'secret' => $secret,
            ]
        ];
    }
}
