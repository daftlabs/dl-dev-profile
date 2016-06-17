<?php
namespace Daftswag\Commands;

use Daftswag\Helpers\Arr;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

abstract class DbCommand extends Command
{
    const ARG_PROJECT = 'project';
    const ARG_ENV = 'env';

    protected function configure()
    {
        return $this
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

    protected function getServiceName(InputInterface $input)
    {
        return "{$input->getArgument(static::ARG_PROJECT)}-{$input->getArgument(static::ARG_ENV)}";
    }

    protected function buildDbConfig(array $taskDefinition)
    {
        $env = [];
        foreach ($taskDefinition['containerDefinitions'][0]['environment'] as $envVar) {
            $env[$envVar['name']] = $envVar['value'];
        }
        return [
            'user' => Arr::pluck($env, ['DB_USER', 'DB_USERNAME', 'MYSQL_USER', 'MYSQL_USERNAME']),
            'pass' => Arr::pluck($env, ['DB_PASS', 'DB_PASSWORD', 'MYSQL_PASS', 'MYSQL_PASSWORD']),
            'host' => Arr::pluck($env, ['DB_HOST', 'MYSQL_HOST']),
            'name' => Arr::pluck($env, ['DB_NAME', 'DB_DATABASE', 'MYSQL_NAME', 'MYSQL_DATABASE']),
        ];
    }
}
