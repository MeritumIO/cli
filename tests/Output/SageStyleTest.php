<?php

namespace Meritum\Cli\Test\Output;

use Meritum\Cli\Output\SageStyle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;

final class SageStyleTest extends TestCase
{
    private function createDefinition(): InputDefinition
    {
        return new InputDefinition([
            new InputArgument('target', InputArgument::OPTIONAL),
            new InputOption('format', null, InputOption::VALUE_OPTIONAL),
            new InputOption('force', null, InputOption::VALUE_NONE),
        ]);
    }

    private function createStyle(array $parameters): SageStyle
    {
        return new SageStyle(new ArrayInput($parameters, $this->createDefinition()), new NullOutput());
    }

    public function test_argument_returns_value_when_provided(): void
    {
        $this->assertSame('foo', $this->createStyle(['target' => 'foo'])->argument('target'));
    }

    public function test_argument_returns_null_when_not_provided(): void
    {
        $this->assertNull($this->createStyle([])->argument('target'));
    }

    public function test_argument_returns_default_when_not_provided(): void
    {
        $this->assertSame('bar', $this->createStyle([])->argument('target', 'bar'));
    }

    public function test_arguments_returns_all_arguments(): void
    {
        $this->assertSame(['target' => 'foo'], $this->createStyle(['target' => 'foo'])->arguments());
    }

    public function test_option_returns_value_when_provided(): void
    {
        $this->assertSame('json', $this->createStyle(['--format' => 'json'])->option('format'));
    }

    public function test_option_returns_null_when_not_provided(): void
    {
        $this->assertNull($this->createStyle([])->option('format'));
    }

    public function test_option_returns_default_when_not_provided(): void
    {
        $this->assertSame('text', $this->createStyle([])->option('format', 'text'));
    }

    public function test_options_returns_all_options(): void
    {
        $options = $this->createStyle(['--format' => 'json'])->options();

        $this->assertSame('json', $options['format']);
    }
}
