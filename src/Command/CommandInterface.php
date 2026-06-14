<?php

namespace Meritum\Cli\Command;

use Meritum\Cli\ExitCode;
use Meritum\Cli\CliKernelInterface;
use Meritum\Cli\Output\SageStyleInterface;

interface CommandInterface
{
    public function __invoke(SageStyleInterface $io): ExitCode;

    public function getName(): string;

    public function getDescription(): string;

    /**
     * @return Argument[]
     */
    public function getArguments(): array;

    /**
     * @return Option[]
     */
    public function getOptions(): array;

    public function getHelp(): string;

    /**
     * @return string[]
     */
    public function getAliases(): array;

    public function isHidden(): bool;

    public function setKernel(CliKernelInterface $kernel): static;
}
