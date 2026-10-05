<?php

namespace Meritum\Cli;

use Psr\Container\ContainerInterface;
use Meritum\Cli\Command\CommandDefinitionInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Georgeff\Kernel\Contract\RunnableKernelInterface;

interface CliKernelInterface extends RunnableKernelInterface
{
    public function setName(string $name): static;

    public function getName(): string;

    public function setVersion(string $version): static;

    public function getVersion(): string;

    /**
     * @param callable(ContainerInterface): mixed $factory
     */
    public function addCommand(string $name, string $id, callable $factory): CommandDefinitionInterface;

    public function handle(?InputInterface $input = null, ?OutputInterface $output = null): int;
}
