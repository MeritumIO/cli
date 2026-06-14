<?php

namespace Meritum\Cli\Output;

use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface SageStyleInterface extends StyleInterface, OutputInterface
{
    public function argument(string $name, mixed $default = null): mixed;

    /**
     * @return array<array-key, mixed>
     */
    public function arguments(): array;

    public function option(string $name, mixed $default = null): mixed;

    /**
     * @return array<array-key, mixed>
     */
    public function options(): array;
}
