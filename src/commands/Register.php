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

        $firstName = $ask("Register's first name", 'samtest');
        $lastName = $ask("Register's last name", 'davistest');
        $github = $ask("Register's github", 'samdavis');
        $email = "{$firstName}@gmail.com";

        $output->writeln('Registering for Gmail...');
        $this->registerForGmail($ask, $output, [
            'givenName' => $firstName,
            'familyName' => $lastName,
            'primaryEmail' => $email,
            'password' => 'insecure'
        ]);
        $output->writeln('Registering for Slack...');
        $this->registerForSlack($output, $email);
        $output->writeln('Registering for Pivotal...');
        $this->registerForPivotal($output, [
            'email' => $email,
            'initials' => $firstName,
        ]);
        $output->writeln('Registering for Github...');
        $this->registerForGitHub($output, $github);
    }

    private function registerForGitHub(OutputInterface $output, $username)
    {
        $gateway = new GitHubGateway(
            $this->globalConfig->get('github_username'),
            $this->globalConfig->get('github_token')
        );
        $output->writeln($gateway->addUserToTeam(GitHubGateway::ENGINEERS_GROUP_ID, $username));
    }

    private function registerForSlack(OutputInterface $output, $email)
    {
        $gateway = new SlackGateway($this->globalConfig->get('slack_token'));
        $output->writeln($gateway->addUserToTeam('daftlabs', $email));
    }

    private function registerForPivotal(OutputInterface $output, $user)
    {
        $gateway = new PivotalGateway(
            $this->config->get('pivotal_id'),
            $this->globalConfig->get('pivotal_token')
        );
        $output->writeln($gateway->inviteUserToProject(GitHubGateway::ENGINEERS_GROUP_ID, [
            'email' => $user['email'],
            'initials' => $user['initials'],
        ]));
    }

    private function registerForGmail(Closure $ask, OutputInterface $output, array $user)
    {
        $gateway = new GoogleGateway(trim(shell_exec("echo {$this->globalConfig->get('google_client_secret_file')}")));
        $authToken = $this->globalConfig->get('google_token');
        if (!$authToken) {
            $authCode = $ask("Google auth code ({$gateway->createAuthUrl()})");
            $authToken = $gateway->generateToken($authCode);
            $this->globalConfig->set('google_token', $authToken);
        }
        $gateway->setToken($authToken['access_token']);
        $output->writeln($gateway->addUser($user));
    }
}
