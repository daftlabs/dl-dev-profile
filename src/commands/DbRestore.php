<?php
namespace Daftswag\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

class DbRestore extends DbBackup
{
    const ARG_FILE = 'file';

    protected function configure()
    {
        parent::configure();
        $this
            ->setDescription('Upload a mysqldump of a project.')
            ->addArgument(
                static::ARG_FILE,
                InputArgument::REQUIRED,
                'Mysqldump to upload.'
            );
    }

    protected function buildCommand(array $db, array $service, array $taskDefinition, InputInterface $input)
    {
        return implode(' ', [
            'mysql',
            "-u {$db['user']}",
            "-p{$db['pass']}",
            "-h {$db['host']}",
            "{$db['name']} < {$input->getArgument(static::ARG_FILE)}"
        ]);
    }
}
