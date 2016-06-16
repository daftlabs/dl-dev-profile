<?php
namespace Daftswag\Commands;

use Daftswag\Helpers\Arr;
use Daftswag\Services\Ec2Gateway;
use Daftswag\Services\EcsGateway;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DbBackup extends Command
{
    const ARG_PROJECT = 'project';
    const ARG_ENV = 'env';

    private $ecsGateway;
    private $ec2Gateway;

    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setDescription('Download a mysqldump of a project.')
            ->addArgument(
                static::ARG_PROJECT,
                InputArgument::REQUIRED,
                'Project name (reference ECS service name)'
            )
            ->addArgument(
                static::ARG_ENV,
                InputArgument::REQUIRED,
                'Project environment (reference ECS service name)'
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
        $taskDefinition = $this->ecsGateway->findServiceTaskDefinition($service);
        $version = array_pop(explode(':', $taskDefinition['containerDefinitions'][0]['image']));
        $backup = implode('.', [$serviceName, $version, date('Y-m-d G:i:s'), 'sql']);
        $env = [];
        foreach ($taskDefinition['containerDefinitions'][0]['environment'] as $envVar) {
            $env[$envVar['name']] = $envVar['value'];
        }
        $db = [
            'user' => Arr::pluck($env, ['DB_USER', 'DB_USERNAME', 'MYSQL_USER', 'MYSQL_USERNAME']),
            'pass' => Arr::pluck($env, ['DB_PASS', 'DB_PASSWORD', 'MYSQL_PASS', 'MYSQL_PASSWORD']),
            'host' => Arr::pluck($env, ['DB_HOST', 'MYSQL_HOST']),
            'name' => Arr::pluck($env, ['DB_NAME', 'DB_DATABASE', 'MYSQL_NAME', 'MYSQL_DATABASE']),
        ];
        $exportCmd = implode(' ', [
            'mysqldump',
            "-u {$db['user']}",
            "-p{$db['pass']}",
            "-h {$db['host']}",
            "{$db['name']} > " . __DIR__ . "/../../db-backups/{$backup}"
        ]);
        echo $exportCmd . "\n";
        echo shell_exec($exportCmd);
    }
}
