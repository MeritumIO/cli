<?php

namespace Meritum\Cli\Command;

use Symfony\Component\Console\Input\InputArgument;

final class Argument
{
    /**
     * @var int<1, 7>
     */
    private int $mode = InputArgument::REQUIRED;

    private string $description = '';

    /**
     * @var string[]
     */
    private array $suggestions = [];

    private mixed $default = null;

    public function __construct(private readonly string $name) {}

    public static function make(string $name): self
    {
        return new self($name);
    }

    public function array(): self
    {
        $this->mode |= InputArgument::IS_ARRAY;

        return $this;
    }

    public function optional(mixed $default = null): self
    {
        $this->mode = ($this->mode & ~InputArgument::REQUIRED) | InputArgument::OPTIONAL;

        $this->default = $default;

        return $this;
    }

    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function suggest(string ...$suggestions): self
    {
        $this->suggestions = [...$this->suggestions, ...$suggestions];

        return $this;
    }

    public function toInputArgument(): InputArgument
    {
        return new InputArgument(
            $this->name,
            $this->mode,
            $this->description,
            $this->default,
            $this->suggestions
        );
    }
}
