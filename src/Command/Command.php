<?php

namespace Meritum\Cli\Command;

use Meritum\Cli\Output\SageStyleInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Meritum\Cli\Exception\CommandNotFoundException;

abstract class Command implements CommandInterface
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function call(string $command, ?SageStyleInterface $output = null, array $parameters = []): int {}
}
