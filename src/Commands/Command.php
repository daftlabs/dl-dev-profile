<?php
namespace Daftswag\Commands;

use Symfony\Component\Console\Command\Command as SymphonyCommand;

class Command extends SymphonyCommand
{
    protected $project;

    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->project = shell_exec('basename `git rev-parse --show-toplevel`');
    }
}
