<?php
namespace Daftswag\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Releases extends Command
{
    const OPT_LIMIT = 'limit';

    protected function configure()
    {
        $this
            ->setName('releases')
            ->setDescription('List Git releases.')
            ->addOption(static::OPT_LIMIT, 'l', InputOption::VALUE_OPTIONAL, 'limits the number of results returned', 5);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(shell_exec(implode(' | ', [
            'git tag',
            'xargs -I@ git log --format=format:"%ai @%n" -1 @',
            'sort -r',
            "awk '{print $4}'",
            "head -n {$input->getOption(static::OPT_LIMIT)}"
        ])));
    }
}
