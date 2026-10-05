<?php

namespace Meritum\Cli\Command;

use Meritum\Cli\Output\SageStyle;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class SymfonyCommandAdaptor extends Command
{
    public function __construct(
        private readonly CommandDefinition $definition,
        private readonly ContainerInterface $container
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName($this->definition->getName())
             ->setDescription($this->definition->getDescription())
             ->setHelp($this->definition->getHelp())
             ->setAliases($this->definition->getAliases())
             ->setHidden($this->definition->isHidden());

        $definition = new InputDefinition();

        foreach ($this->definition->getArguments() as $argument) {
            $definition->addArgument($argument->toInputArgument());
        }

        foreach ($this->definition->getOptions() as $option) {
            $definition->addOption($option->toInputOption());
        }

        $this->setDefinition($definition);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $command = $this->container->get($this->definition->getId());

        if (!$command instanceof CommandInterface) {
            // todo: throw custom exception
            throw new \RuntimeException('Command must be instance of ' . CommandInterface::class);
        }

        return $command(new SageStyle($input, $output))->value;
    }
}
