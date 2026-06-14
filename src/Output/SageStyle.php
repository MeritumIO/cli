<?php

namespace Meritum\Cli\Output;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class SageStyle extends SymfonyStyle implements SageStyleInterface
{
    public function __construct(private readonly InputInterface $input, OutputInterface $output)
    {
        parent::__construct($input, $output);
    }

    public function argument(string $name, mixed $default = null): mixed
    {
        return $this->input->getArgument($name) ?? $default;
    }

    public function arguments(): array
    {
        return $this->input->getArguments();
    }

    public function option(string $name, mixed $default = null): mixed
    {
        return $this->input->getOption($name) ?? $default;
    }

    public function options(): array
    {
        return $this->input->getOptions();
    }
}
