<?php
namespace Daftswag\Commands;

use Daftswag\Helpers\Config;
use Symfony\Component\Console\Command\Command as SymphonyCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Exception;

abstract class Command extends SymphonyCommand
{
    protected $project;
    protected $config;

    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->project = trim(shell_exec('basename `git rev-parse --show-toplevel`'));
        if (!$this->project) {
            throw new Exception('Project not found.');
        }
        $this->config = new Config($this->project);
    }

    protected function getPrompt(InputInterface $input, OutputInterface $output)
    {
        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');
        return function ($text, $default = null) use ($input, $output, $questionHelper) {
            $text .= $default ? " ({$default}):" : ':';
            return $questionHelper->ask($input, $output, new Question($text, $default));
        };
    }
}
