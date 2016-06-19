<?php
namespace Daftswag\Commands;

use Closure;
use Daftswag\Helpers\GlobalConfig;
use Daftswag\Services\GitHubGateway;
use Daftswag\Services\GoogleGateway;
use Daftswag\Services\PivotalGateway;
use Daftswag\Services\SlackGateway;
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
            'pivotal_token' => "Your pivotal API token (https://www.pivotaltracker.com/profile)",
            'slack_token' => "Your slack API token (https://api.slack.com/docs/oauth-test-tokens)",
            'google_client_secret_file' => "Credentials JSON file (https://console.developers.google.com/apis/credentials?project=daftlabs)"
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
        $this->registerForGmail($ask, $output);
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

    private function registerForGmail(Closure $ask, OutputInterface $output)
    {
        $gateway = new GoogleGateway(trim(shell_exec("echo {$this->globalConfig->get('google_client_secret_file')}")));
        $authToken = $this->globalConfig->get('google_token');
        if (!$authToken) {
            $authCode = $ask("Google auth code ({$gateway->createAuthUrl()})");
            $authToken = $gateway->generateToken($authCode);
            $this->globalConfig->set('google_token', $authToken);
        }
        $gateway->setToken($authToken['access_token']);

        if ($email = $ask('Email to invite to Gmail', 'samueljakdavis@aim.com')) {
            $firstName = $ask("Invitee's first name", 'SamTest');
            $lastName = $ask("Invitee's last name", 'DavisTest');
            $output->writeln($gateway->addUser($firstName, $lastName, 'insecure', $email));
        }
    }
}
