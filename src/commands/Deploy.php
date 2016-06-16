<?php
namespace Daftswag\Commands;

use Daftswag\Helpers\Arr;
use Daftswag\Services\Ec2Gateway;
use Daftswag\Services\EcsGateway;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Deploy extends Command
{
    const ARG_PROJECT = 'project';
    const ARG_ENV = 'env';
    const ARG_VERSION = 'version';

    private $ecsGateway;

    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setDescription('Deploy a project.')
            ->addArgument(
                static::ARG_PROJECT,
                InputArgument::REQUIRED,
                'Project name (reference ECS service name)'
            )
            ->addArgument(
                static::ARG_ENV,
                InputArgument::REQUIRED,
                'Project environment (reference ECS service name)'
            )
            ->addArgument(
                static::ARG_VERSION,
                InputArgument::REQUIRED,
                'Project version (reference git tag)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getApplication()->find('db-backup')->execute($input, $output);
        $this->ecsGateway = new EcsGateway($this->project);

        $project = $input->getArgument(static::ARG_PROJECT);
        $env = $input->getArgument(static::ARG_ENV);
        $version = $input->getArgument(static::ARG_VERSION);
        $serviceName = "{$project}-{$env}";

        $service = $this->ecsGateway->findService($serviceName);
        $taskDefinition = $this->ecsGateway->findServiceTaskDefinition($service);
        $newTaskDefinition = $this->buildNewTaskDefinition($taskDefinition, $version);
        $newTask = $this->ecsGateway->registerTask($newTaskDefinition['family'], $newTaskDefinition['containerDefinitions']);
        $updatedService = $this->ecsGateway->updateService($service, $newTask);
        if ($updatedService) {
            $output->writeln("Deploying {$updatedService['taskDefinition']}.");
        } else {
            $output->writeln("Failed to start deploying.");
        }
    }

    private function buildNewTaskDefinition(array $oldTask, $newVersion)
    {

        $newTask = [
            'family' => $oldTask['family'],
            'containerDefinitions' => $oldTask['containerDefinitions']
        ];
        $url = array_shift(explode(':', $newTask['containerDefinitions'][0]['image']));
        $newTask['containerDefinitions'][0]['image'] = implode(':', [$url, $newVersion]);
        foreach ($newTask['containerDefinitions'][0]['environment'] as &$envVar) {
            if ($envVar['name'] === 'APP_VERSION') {
                $envVar['value'] = $newVersion;
            }
        }
        return $newTask;
    }
}
