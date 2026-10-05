<?php

namespace Meritum\Cli\Command;

/**
 * @internal
 */
final class CommandDefinition implements CommandDefinitionInterface
{
    private string $description = '';

    private string $help = '';

    private bool $hidden = false;

    /**
     * @var Argument[]
     */
    private array $arguments = [];

    /**
     * @var Option[]
     */
    private array $options = [];

    /**
     * @var string[]
     */
    private array $aliases = [];

    public function __construct(private readonly string $name, private readonly string $id) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function description(string $content): static
    {
        $this->description = $content;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function help(string $content): static
    {
        $this->help = $content;

        return $this;
    }

    public function getHelp(): string
    {
        return $this->help;
    }

    public function alias(string $alias): static
    {
        $this->aliases[] = $alias;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    public function argument(string $name): Argument
    {
        return $this->arguments[] = new Argument($name);
    }

    /**
     * @return Argument[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function option(string $name): Option
    {
        return $this->options[] = new Option($name);
    }

    /**
     * @return Option[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function hide(bool $condition = true): static
    {
        $this->hidden = $condition;

        return $this;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }
}
