<?php
namespace Daftswag\Commands;

use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Setup extends Command
{
    private $configQuestions;

    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->configQuestions = [
            'pivotal-id' => "Pivotal project id for {$this->project}:",
            'aws-key' => "Your AWS access key id for {$this->project}:",
            'aws-secret' => "Your AWS secret access key for {$this->project}:",
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
        if (!in_array('.git', scandir(getcwd()))) {
            throw new Exception('Must be in project root, .git directory not present.');
        }

        $ask = $this->getPrompt($input, $output);
        foreach ($this->configQuestions as $key => $question) {
            $this->config->set($key, $ask($question, $this->config->get($key)));
        }
    }
}
