<?php

namespace Meritum\Cli;

use Meritum\Cli\Command\CommandInterface;
use Georgeff\Kernel\RunnableKernelInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface CliKernelInterface extends RunnableKernelInterface
{
    public function setName(string $name): static;

    public function getName(): string;

    public function setVersion(string $version): static;

    public function getVersion(): string;

    public function find(string $command): ?CommandInterface;

    public function handle(?InputInterface $input = null, ?OutputInterface $output = null): int;
}
