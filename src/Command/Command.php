<?php

namespace Meritum\Cli\Command;

use Meritum\Cli\CliKernelInterface;
use Meritum\Cli\Output\SageStyleInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Meritum\Cli\Exception\CommandNotFoundException;

abstract class Command implements CommandInterface
{
    private ?string $name = null;

    private string $description = '';

    private string $help = '';

    /**
     * @var string[]
     */
    private array $aliases = [];

    private bool $hidden = false;

    /**
     * @var Argument[]
     */
    private array $arguments = [];

    /**
     * @var Option[]
     */
    private array $options = [];

    private ?CliKernelInterface $kernel = null;

    /**
     * @param array<string, mixed> $parameters
     */
    public function call(string $command, ?SageStyleInterface $output = null, array $parameters = []): int
    {
        if (null === $this->kernel) {
            throw new \LogicException('Cannot call another command before the kernel has been initialized');
        }

        $instance = $this->kernel->find($command);

        if (null === $instance) {
            throw new CommandNotFoundException("Command [$command] was not found");
        }

        return new SymfonyCommandAdaptor($instance)->run(new ArrayInput($parameters), $output ?? new NullOutput());
    }

    public function getName(): string
    {
        if (null === $this->name) {
            throw new \LogicException('Command must have a name set');
        }

        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->throwIfInitialized();

        $this->name = $name;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->throwIfInitialized();

        $this->description = $description;

        return $this;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function addArgument(string $name): Argument
    {
        $this->throwIfInitialized();

        return $this->arguments[] = new Argument($name);
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function addOption(string $name): Option
    {
        $this->throwIfInitialized();

        return $this->options[] = new Option($name);
    }

    public function getHelp(): string
    {
        return $this->help;
    }

    public function setHelp(string $help): static
    {
        $this->throwIfInitialized();

        $this->help = $help;

        return $this;
    }

    public function getAliases(): array
    {
        return $this->aliases;
    }

    public function addAlias(string $alias): static
    {
        $this->throwIfInitialized();

        $this->aliases[] = $alias;

        return $this;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function shouldHide(bool $hide = true): static
    {
        $this->throwIfInitialized();

        $this->hidden = $hide;

        return $this;
    }

    public function setKernel(CliKernelInterface $kernel): static
    {
        $this->throwIfInitialized();

        $this->kernel = $kernel;

        return $this;
    }

    private function throwIfInitialized(): void
    {
        if (null !== $this->kernel) {
            throw new \LogicException('Command has already been initialized, cannot modify configuration');
        }
    }
}
