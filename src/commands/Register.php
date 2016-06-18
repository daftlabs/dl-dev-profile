<?php
namespace Daftswag\Commands;

use Closure;
use Daftswag\Helpers\GlobalConfig;
use Daftswag\Services\GitHubGateway;
use Daftswag\Services\PivotalGateway;
use Daftswag\Services\SlackGateway;
use Frlnc\Slack\Core\Commander;
use Frlnc\Slack\Http\CurlInteractor;
use Frlnc\Slack\Http\SlackResponseFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Register extends Command
{
    private $globalConfig;
    private $globalQuestions;

    public function __construct($name = null)
    {
        parent::__construct($name);

        $this->globalConfig = new GlobalConfig();
        $this->globalQuestions = [
            'github_username' => "Your github username",
            'github_token' => "Your github API token (https://github.com/settings/tokens)",
            'slack_token' => "Your slack API token (https://api.slack.com/docs/oauth-test-tokens)",
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

        $this->registerForGitHub($ask, $output);
        $this->registerForPivotal($ask, $output);
        $this->registerForSlack($ask, $output);
    }

    private function registerForGitHub(Closure $ask, OutputInterface $output)
    {
        $gateway = new GitHubGateway(
            $this->globalConfig->get('github_username'),
            $this->globalConfig->get('github_token')
        );
        if ($username = $ask('Username to invite to GitHub')) {
            $output->writeln($gateway->addUserToTeam(GitHubGateway::ENGINEERS_GROUP_ID, $username));
        }
    }

    private function registerForPivotal(Closure $ask, OutputInterface $output)
    {
        $gateway = new PivotalGateway(
            $this->config->get('pivotal_id'),
            $this->globalConfig->get('pivotal_token')
        );
        if ($email = $ask('Email to invite to Pivotal Tracker', 'samueljakdavis@gmail.com')) {
            $initials = $ask("Pivotal Tracker invitee's initials", 'sjd');
            $output->writeln($gateway->inviteUserToProject(GitHubGateway::ENGINEERS_GROUP_ID, [
                'email' => $email,
                'initials' => $initials,
            ]));
        }
    }

    private function registerForSlack(Closure $ask, OutputInterface $output)
    {
        if ($email = $ask('Email to invite to Slack', 'samueljakdavis@gmail.com')) {
            $gateway = new SlackGateway($this->globalConfig->get('slack_token'));
            $output->writeln($gateway->addUserToTeam('daftlabs', $email));
        }
    }
}
