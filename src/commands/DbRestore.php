<?php
namespace Daftswag\Commands;

use Daftswag\Services\EcsGateway;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DbRestore extends DbCommand
{
    const ARG_FILE = 'file';

    private $ecsGateway;

    protected function configure()
    {
        parent::configure()
            ->setDescription('Upload a mysqldump of a project.')
            ->addArgument(
                static::ARG_FILE,
                InputArgument::REQUIRED,
                'Mysqldump to upload.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ecsGateway = new EcsGateway($this->project);

        $service = $this->ecsGateway->findService($this->getServiceName($input));
        $taskDefinition = $this->ecsGateway->findServiceTaskDefinition($service);
        $db = $this->buildDbConfig($taskDefinition);
        $cmd = implode(' ', [
            'mysql',
            "-u {$db['user']}",
            "-p{$db['pass']}",
            "-h {$db['host']}",
            "{$db['name']} < {$input->getArgument(static::ARG_FILE)}"
        ]);

        echo "{$cmd}\n";
        echo shell_exec($cmd);
    }
}
