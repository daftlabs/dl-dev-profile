<?php
namespace Daftswag\Commands;

use Daftswag\Services\Ec2Gateway;
use Daftswag\Services\EcsGateway;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Describe extends Command
{
    const ARG_PROJECT = 'project';
    const ARG_ENV = 'env';

    /** @var EcsGateway */
    private $ecsGateway;
    /** @var Ec2Gateway */
    private $ec2Gateway;

    protected function configure()
    {
        $this
            ->setDescription('Describe a project.')
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ecsGateway = new EcsGateway($this->project);
        $this->ec2Gateway = new Ec2Gateway($this->project);

        $project = $input->getArgument(static::ARG_PROJECT);
        $env = $input->getArgument(static::ARG_ENV);
        $serviceName = "{$project}-{$env}";

        $service = $this->ecsGateway->findService($serviceName);
        $taskDefinition = $this->ecsGateway->findServiceTaskDefinition($service);
        $output->writeln($this->formatService($service));
        $output->writeln($this->formatDefinition($taskDefinition['containerDefinitions'][0]));
    }

    private function formatService(array $service)
    {
        $instanceIds = $this->ecsGateway->getInstanceIdsByService($service);
        $output = array_merge(
            $this->generateHeader("SERVICE: {$service['serviceName']}"),
            [
                "Desired: {$service['desiredCount']}",
                "Pending: {$service['pendingCount']}",
                "Running: {$service['runningCount']}",
            ],
            $this->generateHeader('HOSTS'),
            $this->ec2Gateway->getHosts($instanceIds),
            $this->generateHeader('DEPLOYMENTS: ' . count($service['deployments']))
        );
        foreach ($service['deployments'] as $deployment) {
            $output = array_merge($output, [
                "Status: {$deployment['status']} ({$deployment['createdAt']})",
                "Desired: {$service['desiredCount']}",
                "Pending: {$service['pendingCount']}",
                "Running: {$service['runningCount']}",
            ]);
        }
        return $output;
    }

    private function generateHeader($text)
    {
        $text = "|| {$text} ||";
        return [
            null,
            str_pad("", strlen($text), "="),
            $text,
            str_pad("", strlen($text), "="),
        ];
    }

    private function formatDefinition(array $definition)
    {
        $output = array_merge(
            $this->generateHeader("DEFINITION: {$definition['image']}"),
            [
                "CPU: {$definition['cpu']}",
                "Memory: {$definition['memory']}",
            ],
            $this->generateHeader('ENVIRONMENT')
        );
        foreach ($definition['environment'] as $envVar) {
            $output[] = "{$envVar['name']}={$envVar['value']}";
        }
        return $output;
    }
}
