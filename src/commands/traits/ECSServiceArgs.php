<?php

namespace Daftswag\Commands\Traits;

use Daftswag\Commands\Command;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @method Command addArgument($name, $type, $description, $default = null)
 */
trait ECSServiceArgs
{
    protected function addServiceArgs()
    {
        return $this
            ->addArgument(
                static::ARG_PROJECT,
                InputArgument::REQUIRED,
                'Project name (reference ECS service name)'
            )
            ->addArgument(
                static::ARG_ENV,
                InputArgument::REQUIRED,
                'Project environment (reference ECS service name)'
            );
    }
}
