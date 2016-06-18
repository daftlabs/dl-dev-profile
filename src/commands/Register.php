<?php
namespace Daftswag\Commands;

use Daftswag\Helpers\GlobalConfig;
use Daftswag\Services\GitHubGateway;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Register extends Command
{
    private $globalConfig;
    private $globalQuestions;
    private $gitHubGateway;

    public function __construct($name = null)
    {
        parent::__construct($name);

        $this->globalConfig = new GlobalConfig();
        $this->globalQuestions = [
            'github_username' => "Your github username",
            'github_token' => "Your github API token (https://github.com/settings/tokens)",
        ];
    }

    protected function configure()
    {
        $this->setDescription('Assign someone to the daftlabs GitHub group.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ask = $this->getPrompt($input, $output);
        foreach ($this->globalQuestions as $key => $question) {
            $this->globalConfig->set($key, $ask($question, $this->globalConfig->get($key)));
        }

        $this->gitHubGateway = new GitHubGateway(
            $this->globalConfig->get('github_username'),
            $this->globalConfig->get('github_token')
        );
        if ($gitUsername = $ask('Github Username to invite')) {
            $output->writeln($this->gitHubGateway->addUserToTeam('engineers', $gitUsername));
        }

    }
}
