<?php

namespace Meritum\Cli\Command;

use Symfony\Component\Console\Input\InputOption;

final class Option
{
    private const int VALUE_MODE_MASK = InputOption::VALUE_NONE | InputOption::VALUE_REQUIRED | InputOption::VALUE_OPTIONAL;

    /**
     * @var int<0, 31>
     */
    private int $mode = InputOption::VALUE_NONE;

    /**
     * @var string[]
     */
    private array $shortcuts = [];

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

    public function required(): self
    {
        $this->mode = ($this->mode & ~self::VALUE_MODE_MASK) | InputOption::VALUE_REQUIRED;

        return $this;
    }

    public function optional(mixed $default = null): self
    {
        $this->mode = ($this->mode & ~self::VALUE_MODE_MASK) | InputOption::VALUE_OPTIONAL;

        $this->default = $default;

        return $this;
    }

    public function array(): self
    {
        $this->mode |= InputOption::VALUE_IS_ARRAY;

        return $this;
    }

    public function negatable(): self
    {
        $this->mode |= InputOption::VALUE_NEGATABLE;

        return $this;
    }

    public function shortcut(string ...$shortcuts): self
    {
        $this->shortcuts = [...$this->shortcuts, ...$shortcuts];

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

    public function toInputOption(): InputOption
    {
        return new InputOption(
            $this->name,
            $this->shortcuts,
            $this->mode,
            $this->description,
            $this->default,
            $this->suggestions
        );
    }
}
