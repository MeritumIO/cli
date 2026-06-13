<?php

namespace Meritum\Cli\Test\Command;

use Meritum\Cli\Command\Argument;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputArgument;

final class ArgumentTest extends TestCase
{
    public function test_make_returns_argument_instance(): void
    {
        $this->assertInstanceOf(Argument::class, Argument::make('name'));
    }

    public function test_defaults_to_required(): void
    {
        $argument = Argument::make('name')->toInputArgument();

        $this->assertTrue($argument->isRequired());
    }

    public function test_optional_makes_argument_optional(): void
    {
        $argument = Argument::make('name')->optional()->toInputArgument();

        $this->assertFalse($argument->isRequired());
    }

    public function test_optional_sets_default_value(): void
    {
        $argument = Argument::make('name')->optional('fallback')->toInputArgument();

        $this->assertSame('fallback', $argument->getDefault());
    }

    public function test_array_makes_argument_an_array(): void
    {
        $argument = Argument::make('name')->array()->toInputArgument();

        $this->assertTrue($argument->isArray());
    }

    public function test_array_preserves_required(): void
    {
        $argument = Argument::make('name')->array()->toInputArgument();

        $this->assertTrue($argument->isRequired());
    }

    public function test_optional_then_array_produces_optional_array(): void
    {
        $argument = Argument::make('name')->optional()->array()->toInputArgument();

        $this->assertTrue($argument->isArray());
        $this->assertFalse($argument->isRequired());
    }

    public function test_array_then_optional_produces_optional_array(): void
    {
        $argument = Argument::make('name')->array()->optional()->toInputArgument();

        $this->assertTrue($argument->isArray());
        $this->assertFalse($argument->isRequired());
    }

    public function test_description_is_set(): void
    {
        $argument = Argument::make('name')->description('The target name')->toInputArgument();

        $this->assertSame('The target name', $argument->getDescription());
    }

    public function test_suggest_marks_argument_as_having_suggestions(): void
    {
        $argument = Argument::make('name')->suggest('foo', 'bar')->toInputArgument();

        $this->assertTrue($argument->hasCompletion());
    }

    public function test_suggest_accumulates_across_calls(): void
    {
        $argument = Argument::make('name')->suggest('foo')->suggest('bar')->toInputArgument();

        $this->assertTrue($argument->hasCompletion());
    }

    public function test_to_input_argument_sets_name(): void
    {
        $argument = Argument::make('target')->toInputArgument();

        $this->assertSame('target', $argument->getName());
    }
}
