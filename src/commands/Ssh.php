<?php
namespace Daftswag\Commands;

use Daftswag\Commands\Traits\ECSServiceArgs;
use Daftswag\Services\Ec2Gateway;
use Daftswag\Services\EcsGateway;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Ssh extends Command
{
    use ECSServiceArgs;

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
            ->addServiceArgs()
            ->setDescription('Run a bash command in ECS containers for a service.')
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
        $projectPem = $this->exec("echo ~/.ssh/{$this->project}.pem", false);

        $project = $input->getArgument(static::ARG_PROJECT);
        $env = $input->getArgument(static::ARG_ENV);
        $serviceName = "{$project}-{$env}";
        $service = $this->ecsGateway->findService($serviceName);
        $instanceIds = $this->ecsGateway->getInstanceIdsByService($service);
        $hosts = $this->ec2Gateway->getHosts($instanceIds);
        $pem = is_file($projectPem) ? $projectPem : null;

        foreach ($hosts as $host) {
            foreach (explode("\n", $this->ec2Gateway->runCmd($host, 'docker ps', $pem)) as $containerDescription) {
                if (stristr($containerDescription, $serviceName)) {
                    $description = preg_split('/\s{2,}/', $containerDescription);
                    $res = $this->ec2Gateway->runCmd($host, "docker exec -i {$description[0]} {$input->getArgument(static::ARG_CMD)}", $pem);
                    echo implode("\n", ['==============', $host, '==============', $res]) . "\n";
                    break;
                }
            }
        }
    }
}
