<?php

namespace Meritum\Cli\Command;

use Meritum\Cli\Output\SageStyle;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class SymfonyCommandAdaptor extends Command
{
    public function __construct(private readonly CommandInterface $command)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName($this->command->getName())
             ->setDescription($this->command->getDescription())
             ->setHelp($this->command->getHelp())
             ->setAliases($this->command->getAliases())
             ->setHidden($this->command->isHidden());

        $definition = new InputDefinition();

        foreach ($this->command->getArguments() as $argument) {
            $definition->addArgument($argument->toInputArgument());
        }

        foreach ($this->command->getOptions() as $option) {
            $definition->addOption($option->toInputOption());
        }

        $this->setDefinition($definition);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SageStyle($input, $output);

        return ($this->command)($input, $style)->value;
    }
}
