<?php

namespace Meritum\Cli\Exception;

use Throwable;
use Meritum\Cli\Output\SageStyleInterface;
use Symfony\Component\Console\Input\InputInterface;

interface ExceptionHandlerInterface
{
    public function handle(Throwable $exception, InputInterface $input, SageStyleInterface $output): void;
}
