<?php

namespace Meritum\Cli\Test\Command;

use Meritum\Cli\Command\Option;
use Meritum\Cli\Command\Argument;
use PHPUnit\Framework\TestCase;
use Meritum\Cli\Command\CommandDefinition;
use Meritum\Cli\Command\CommandDefinitionInterface;

final class CommandDefinitionTest extends TestCase
{
    private function createDefinition(): CommandDefinition
    {
        return new CommandDefinition('test:command', 'test.command');
    }

    public function test_implements_command_definition_interface(): void
    {
        $this->assertInstanceOf(CommandDefinitionInterface::class, $this->createDefinition());
    }

    public function test_get_name_returns_name(): void
    {
        $this->assertSame('test:command', $this->createDefinition()->getName());
    }

    public function test_get_id_returns_id(): void
    {
        $this->assertSame('test.command', $this->createDefinition()->getId());
    }

    public function test_description_defaults_to_empty(): void
    {
        $this->assertSame('', $this->createDefinition()->getDescription());
    }

    public function test_description_returns_static(): void
    {
        $definition = $this->createDefinition();

        $this->assertSame($definition, $definition->description('Does something'));
    }

    public function test_description_updates_description(): void
    {
        $this->assertSame('Does something', $this->createDefinition()->description('Does something')->getDescription());
    }

    public function test_help_defaults_to_empty(): void
    {
        $this->assertSame('', $this->createDefinition()->getHelp());
    }

    public function test_help_returns_static(): void
    {
        $definition = $this->createDefinition();

        $this->assertSame($definition, $definition->help('Extended help'));
    }

    public function test_help_updates_help(): void
    {
        $this->assertSame('Extended help', $this->createDefinition()->help('Extended help')->getHelp());
    }

    public function test_aliases_default_to_empty(): void
    {
        $this->assertSame([], $this->createDefinition()->getAliases());
    }

    public function test_alias_returns_static(): void
    {
        $definition = $this->createDefinition();

        $this->assertSame($definition, $definition->alias('tc'));
    }

    public function test_alias_accumulates_aliases(): void
    {
        $this->assertSame(['tc', 'tcommand'], $this->createDefinition()->alias('tc')->alias('tcommand')->getAliases());
    }

    public function test_is_hidden_defaults_to_false(): void
    {
        $this->assertFalse($this->createDefinition()->isHidden());
    }

    public function test_hide_returns_static(): void
    {
        $definition = $this->createDefinition();

        $this->assertSame($definition, $definition->hide());
    }

    public function test_hide_sets_hidden(): void
    {
        $this->assertTrue($this->createDefinition()->hide()->isHidden());
    }

    public function test_hide_with_false_unsets_hidden(): void
    {
        $this->assertFalse($this->createDefinition()->hide()->hide(false)->isHidden());
    }

    public function test_arguments_default_to_empty(): void
    {
        $this->assertSame([], $this->createDefinition()->getArguments());
    }

    public function test_argument_returns_argument_instance(): void
    {
        $this->assertInstanceOf(Argument::class, $this->createDefinition()->argument('target'));
    }

    public function test_argument_appends_to_arguments(): void
    {
        $definition = $this->createDefinition();
        $argument   = $definition->argument('target');

        $this->assertSame([$argument], $definition->getArguments());
    }

    public function test_options_default_to_empty(): void
    {
        $this->assertSame([], $this->createDefinition()->getOptions());
    }

    public function test_option_returns_option_instance(): void
    {
        $this->assertInstanceOf(Option::class, $this->createDefinition()->option('force'));
    }

    public function test_option_appends_to_options(): void
    {
        $definition = $this->createDefinition();
        $option     = $definition->option('force');

        $this->assertSame([$option], $definition->getOptions());
    }
}
