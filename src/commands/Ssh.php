<?php
namespace Daftswag\Commands;

use Daftswag\Services\Ec2Gateway;
use Daftswag\Services\EcsGateway;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Ssh extends Command
{
    const ARG_PROJECT = 'project';
    const ARG_ENV = 'env';
    const ARG_CMD = 'cmd';

    private $ecsGateway;
    private $ec2Gateway;

    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('ssh')
            ->setDescription('Initialize a repository')
            ->addArgument(
                static::ARG_PROJECT,
                InputArgument::REQUIRED,
                'Project name (reference ECS service name)'
            )
            ->addArgument(
                static::ARG_ENV,
                InputArgument::REQUIRED,
                'Project environment (reference ECS service name)'
            )
            ->addArgument(
                static::ARG_CMD,
                InputArgument::REQUIRED,
                'The command to be run on the various ECS containers.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ecsGateway = new EcsGateway($this->project);
        $this->ec2Gateway = new Ec2Gateway($this->project);

        $project = $input->getArgument(static::ARG_PROJECT);
        $env = $input->getArgument(static::ARG_ENV);
        $serviceName = "{$project}-{$env}";
        $service = $this->ecsGateway->findService($serviceName);
        $instanceIds = $this->ecsGateway->getInstanceIdsByService($service);
        $hosts = $this->ec2Gateway->getHosts($instanceIds);

        foreach ($hosts as $host) {
            foreach (explode("\n", $this->ec2Gateway->runCmd($host, 'docker ps')) as $containerDescription) {
                if (stristr($containerDescription, $serviceName)) {
                    $description = preg_split('/\s{2,}/', $containerDescription);
                    $res = $this->ec2Gateway->runCmd($host, "docker exec -i {$description[0]} {$input->getArgument(static::ARG_CMD)}");
                    echo implode("\n", ['==============', $host, '==============', $res]) . "\n";
                    break;
                }
            }
        }
    }
}
