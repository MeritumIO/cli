<?php

namespace Meritum\Cli\Exception;

use Throwable;
use Meritum\Cli\Output\SageStyleInterface;

interface ExceptionHandlerInterface
{
    public function handle(Throwable $exception, SageStyleInterface $io): void;
}
