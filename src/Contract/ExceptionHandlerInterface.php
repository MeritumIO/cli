<?php

namespace Meritum\Cli\Contract;

use Throwable;
use Meritum\Cli\Output\SageStyleInterface;

interface ExceptionHandlerInterface
{
    public function handle(Throwable $exception, SageStyleInterface $io): void;
}
