<?php
namespace Daftswag\Commands;

use Daftswag\Services\EcsGateway;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DbBackup extends DbCommand
{
    const ARG_PROJECT = 'project';
    const ARG_ENV = 'env';

    private $ecsGateway;

    protected function configure()
    {
        parent::configure()->setDescription('Download a mysqldump of a project.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ecsGateway = new EcsGateway($this->project);

        $service = $this->ecsGateway->findService($this->getServiceName($input));
        $taskDefinition = $this->ecsGateway->findServiceTaskDefinition($service);
        $version = array_pop(explode(':', $taskDefinition['containerDefinitions'][0]['image']));
        $backup = implode('.', [$service['serviceName'], $version, date('Y-m-d-G:i:s'), 'sql']);
        $db = $this->buildDbConfig($taskDefinition);
        $cmd = implode(' ', [
            'mysqldump',
            "-u {$db['user']}",
            "-p{$db['pass']}",
            "-h {$db['host']}",
            "{$db['name']} > " . __DIR__ . "/../../db-backups/{$backup}"
        ]);

        $this->exec($cmd);
    }
}
