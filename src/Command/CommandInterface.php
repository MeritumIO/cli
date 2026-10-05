<?php

namespace Meritum\Cli\Command;

use Meritum\Cli\ExitCode;
use Meritum\Cli\Output\SageStyleInterface;

interface CommandInterface
{
    public function __invoke(SageStyleInterface $io): ExitCode;
}
