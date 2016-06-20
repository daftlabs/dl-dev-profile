<?php
namespace Daftswag\Commands;

use Daftswag\Commands\Traits\ECSServiceArgs;
use Daftswag\Services\EcsGateway;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DbRestore extends DbCommand
{
    use ECSServiceArgs;

    const ARG_FILE = 'file';

    private $ecsGateway;

    protected function configure()
    {
        $this
            ->addServiceArgs()
            ->setDescription('Upload a mysqldump of a project.')
            ->addArgument(
                static::ARG_FILE,
                InputArgument::REQUIRED,
                'Mysqldump to upload.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getApplication()->find('db-backup')->execute($input, $output);
        $this->ecsGateway = new EcsGateway($this->project);

        $service = $this->ecsGateway->findService($this->getServiceName($input));
        $taskDefinition = $this->ecsGateway->findServiceTaskDefinition($service);
        $db = $this->buildDbConfig($taskDefinition);
        $connectCmd = implode(' ', [
            'mysql',
            "-u {$db['user']}",
            "-p{$db['pass']}",
            "-h {$db['host']}",

        ]);
        $dropCmd = "{$connectCmd} -e \"DROP DATABASE {$db['name']}; CREATE DATABASE {$db['name']};\"";
        $restoreCmd = "{$connectCmd} {$db['name']} < {$input->getArgument(static::ARG_FILE)}";
        $this->exec($dropCmd);
        $this->exec($restoreCmd);
    }
}
