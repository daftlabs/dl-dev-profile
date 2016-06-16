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

    private $ecsGateway;
    private $ec2Gateway;

    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->ecsGateway = new EcsGateway($this->project);
        $this->ec2Gateway = new Ec2Gateway($this->project);
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
                'cmd',
                InputArgument::REQUIRED,
                'The command to be run on the various ECS containers.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $project = $input->getArgument(static::ARG_PROJECT);
        $env = $input->getArgument(static::ARG_ENV);
        $serviceName = "{$project}-{$env}";
        $service = $this->ecsGateway->findService($serviceName);
        $instanceIds = $this->ecsGateway->getInstanceIdsByService($service);
        $hosts = $this->ec2Gateway->getHosts($instanceIds);

        die(print_r($hosts, true));
    }
}
