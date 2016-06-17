<?php
namespace Daftswag\Commands;

use Daftswag\Helpers\GlobalConfig;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Setup extends Command
{
    private $globalConfig;
    private $globalQuestions;
    private $configQuestions;

    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->globalConfig = new GlobalConfig();
        $this->globalQuestions = [
            'pivotal_token' => "Your pivotal API token (https://www.pivotaltracker.com/profile)",
        ];
        $this->configQuestions = [
            'pivotal_id' => "Pivotal project id for {$this->project}",
            'aws_access_key_id' => "Your AWS access key id for {$this->project}",
            'aws_secret_access_key' => "Your AWS secret access key for {$this->project}",
        ];
    }

    protected function configure()
    {
        $this
            ->setName('setup')
            ->setDescription('Initialize a repository');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ask = $this->getPrompt($input, $output);
        foreach ($this->globalQuestions as $key => $question) {
            $this->globalConfig->set($key, $ask($question, $this->globalConfig->get($key)));
        }
        foreach ($this->configQuestions as $key => $question) {
            $this->config->set($key, $ask($question, $this->config->get($key)));
        }
    }
}
