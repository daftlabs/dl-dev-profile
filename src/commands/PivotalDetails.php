<?php
namespace Daftswag\Commands;

use Daftswag\Helpers\Config;
use Daftswag\Services\PivotalGateway;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PivotalDetails extends Command
{
    const ARG_PIVOTAL_IDS = 'pivotal_ids';

    private $globalConfig;
    private $pivotalGateway;

    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->globalConfig = new Config();
    }

    protected function configure()
    {
        $this
            ->setDescription('List details of pivotal stories.')
            ->addArgument(
                static::ARG_PIVOTAL_IDS,
                InputArgument::IS_ARRAY,
                'SSV of pivotal ids to get details on.',
                [shell_exec('git rev-parse --abbrev-ref HEAD')]
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->pivotalGateway = new PivotalGateway($this->config->get('pivotal_id'), $this->globalConfig->get('pivotal_token'));
        $stories = $this->pivotalGateway->describeStories($input->getArgument(static::ARG_PIVOTAL_IDS));
        $output->writeln(array_map(function ($story) { return json_encode($story, JSON_PRETTY_PRINT); }, $stories));
    }
}
