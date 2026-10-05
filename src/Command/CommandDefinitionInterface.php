<?php

namespace Meritum\Cli\Command;

interface CommandDefinitionInterface
{
    public function description(string $content): static;

    public function help(string $content): static;

    public function alias(string $alias): static;

    public function hide(bool $condition): static;

    public function argument(string $name): Argument;

    public function option(string $name): Option;
}
