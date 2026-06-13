<?php

namespace Meritum\Cli\Test\Command;

use Meritum\Cli\Command\Option;
use PHPUnit\Framework\TestCase;

final class OptionTest extends TestCase
{
    public function test_make_returns_option_instance(): void
    {
        $this->assertInstanceOf(Option::class, Option::make('force'));
    }

    public function test_defaults_to_value_none(): void
    {
        $option = Option::make('force')->toInputOption();

        $this->assertFalse($option->acceptValue());
    }

    public function test_required_sets_value_required(): void
    {
        $option = Option::make('output')->required()->toInputOption();

        $this->assertTrue($option->isValueRequired());
    }

    public function test_optional_sets_value_optional(): void
    {
        $option = Option::make('output')->optional()->toInputOption();

        $this->assertTrue($option->isValueOptional());
    }

    public function test_optional_sets_default_value(): void
    {
        $option = Option::make('output')->optional('stdout')->toInputOption();

        $this->assertSame('stdout', $option->getDefault());
    }

    public function test_array_makes_option_an_array(): void
    {
        $option = Option::make('tag')->required()->array()->toInputOption();

        $this->assertTrue($option->isArray());
    }

    public function test_array_preserves_required(): void
    {
        $option = Option::make('tag')->required()->array()->toInputOption();

        $this->assertTrue($option->isValueRequired());
    }

    public function test_array_preserves_optional(): void
    {
        $option = Option::make('tag')->optional()->array()->toInputOption();

        $this->assertTrue($option->isValueOptional());
    }

    public function test_required_then_array_produces_required_array(): void
    {
        $option = Option::make('tag')->required()->array()->toInputOption();

        $this->assertTrue($option->isArray());
        $this->assertTrue($option->isValueRequired());
    }

    public function test_array_then_required_produces_required_array(): void
    {
        $option = Option::make('tag')->array()->required()->toInputOption();

        $this->assertTrue($option->isArray());
        $this->assertTrue($option->isValueRequired());
    }

    public function test_negatable_makes_option_negatable(): void
    {
        $option = Option::make('ansi')->negatable()->toInputOption();

        $this->assertTrue($option->isNegatable());
    }

    public function test_shortcut_is_set(): void
    {
        $option = Option::make('output')->shortcut('o')->toInputOption();

        $this->assertSame('o', $option->getShortcut());
    }

    public function test_shortcut_accumulates_across_calls(): void
    {
        $option = Option::make('output')->shortcut('o')->shortcut('O')->toInputOption();

        $this->assertSame('o|O', $option->getShortcut());
    }

    public function test_description_is_set(): void
    {
        $option = Option::make('output')->description('Output path')->toInputOption();

        $this->assertSame('Output path', $option->getDescription());
    }

    public function test_suggest_marks_option_as_having_completion(): void
    {
        $option = Option::make('format')->required()->suggest('json', 'xml')->toInputOption();

        $this->assertTrue($option->hasCompletion());
    }

    public function test_suggest_accumulates_across_calls(): void
    {
        $option = Option::make('format')->required()->suggest('json')->suggest('xml')->toInputOption();

        $this->assertTrue($option->hasCompletion());
    }

    public function test_to_input_option_sets_name(): void
    {
        $option = Option::make('output')->toInputOption();

        $this->assertSame('output', $option->getName());
    }
}
