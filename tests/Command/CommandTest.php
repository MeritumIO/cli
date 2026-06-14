<?php

namespace Meritum\Cli\Test\Command;

use Meritum\Cli\CliKernelInterface;
use Meritum\Cli\Command\Argument;
use Meritum\Cli\Command\Command;
use Meritum\Cli\Command\Option;
use Meritum\Cli\ExitCode;
use Meritum\Cli\Output\SageStyleInterface;
use PHPUnit\Framework\TestCase;

final class CommandTest extends TestCase
{
    private function createCommand(): Command
    {
        return new class extends Command {
            public function __invoke(SageStyleInterface $io): ExitCode
            {
                return ExitCode::Success;
            }
        };
    }

    private function createKernel(): CliKernelInterface
    {
        return $this->createMock(CliKernelInterface::class);
    }

    public function test_get_name_throws_when_not_set(): void
    {
        $this->expectException(\LogicException::class);

        $this->createCommand()->getName();
    }

    public function test_set_name_returns_static(): void
    {
        $command = $this->createCommand();

        $this->assertSame($command, $command->setName('test:command'));
    }

    public function test_set_name_updates_name(): void
    {
        $command = $this->createCommand();
        $command->setName('test:command');

        $this->assertSame('test:command', $command->getName());
    }

    public function test_set_name_throws_after_initialized(): void
    {
        $command = $this->createCommand();
        $command->setName('test:command');
        $command->setKernel($this->createKernel());

        $this->expectException(\LogicException::class);

        $command->setName('other:command');
    }

    public function test_get_description_defaults_to_empty(): void
    {
        $this->assertSame('', $this->createCommand()->getDescription());
    }

    public function test_set_description_returns_static(): void
    {
        $command = $this->createCommand();

        $this->assertSame($command, $command->setDescription('Does something'));
    }

    public function test_set_description_updates_description(): void
    {
        $command = $this->createCommand();
        $command->setDescription('Does something');

        $this->assertSame('Does something', $command->getDescription());
    }

    public function test_set_description_throws_after_initialized(): void
    {
        $command = $this->createCommand();
        $command->setName('test:command');
        $command->setKernel($this->createKernel());

        $this->expectException(\LogicException::class);

        $command->setDescription('Does something');
    }

    public function test_get_help_defaults_to_empty(): void
    {
        $this->assertSame('', $this->createCommand()->getHelp());
    }

    public function test_set_help_returns_static(): void
    {
        $command = $this->createCommand();

        $this->assertSame($command, $command->setHelp('Extended help'));
    }

    public function test_set_help_updates_help(): void
    {
        $command = $this->createCommand();
        $command->setHelp('Extended help');

        $this->assertSame('Extended help', $command->getHelp());
    }

    public function test_set_help_throws_after_initialized(): void
    {
        $command = $this->createCommand();
        $command->setName('test:command');
        $command->setKernel($this->createKernel());

        $this->expectException(\LogicException::class);

        $command->setHelp('Extended help');
    }

    public function test_get_aliases_defaults_to_empty(): void
    {
        $this->assertSame([], $this->createCommand()->getAliases());
    }

    public function test_add_alias_returns_static(): void
    {
        $command = $this->createCommand();

        $this->assertSame($command, $command->addAlias('tc'));
    }

    public function test_add_alias_accumulates_aliases(): void
    {
        $command = $this->createCommand();
        $command->addAlias('tc')->addAlias('tcommand');

        $this->assertSame(['tc', 'tcommand'], $command->getAliases());
    }

    public function test_add_alias_throws_after_initialized(): void
    {
        $command = $this->createCommand();
        $command->setName('test:command');
        $command->setKernel($this->createKernel());

        $this->expectException(\LogicException::class);

        $command->addAlias('tc');
    }

    public function test_is_hidden_defaults_to_false(): void
    {
        $this->assertFalse($this->createCommand()->isHidden());
    }

    public function test_should_hide_returns_static(): void
    {
        $command = $this->createCommand();

        $this->assertSame($command, $command->shouldHide());
    }

    public function test_should_hide_sets_hidden(): void
    {
        $command = $this->createCommand();
        $command->shouldHide();

        $this->assertTrue($command->isHidden());
    }

    public function test_should_hide_throws_after_initialized(): void
    {
        $command = $this->createCommand();
        $command->setName('test:command');
        $command->setKernel($this->createKernel());

        $this->expectException(\LogicException::class);

        $command->shouldHide();
    }

    public function test_add_argument_returns_argument_instance(): void
    {
        $this->assertInstanceOf(Argument::class, $this->createCommand()->addArgument('target'));
    }

    public function test_add_argument_appends_to_arguments(): void
    {
        $command = $this->createCommand();
        $command->addArgument('target');

        $this->assertCount(1, $command->getArguments());
    }

    public function test_add_argument_throws_after_initialized(): void
    {
        $command = $this->createCommand();
        $command->setName('test:command');
        $command->setKernel($this->createKernel());

        $this->expectException(\LogicException::class);

        $command->addArgument('target');
    }

    public function test_add_option_returns_option_instance(): void
    {
        $this->assertInstanceOf(Option::class, $this->createCommand()->addOption('force'));
    }

    public function test_add_option_appends_to_options(): void
    {
        $command = $this->createCommand();
        $command->addOption('force');

        $this->assertCount(1, $command->getOptions());
    }

    public function test_add_option_throws_after_initialized(): void
    {
        $command = $this->createCommand();
        $command->setName('test:command');
        $command->setKernel($this->createKernel());

        $this->expectException(\LogicException::class);

        $command->addOption('force');
    }

    public function test_set_kernel_returns_static(): void
    {
        $command = $this->createCommand();
        $command->setName('test:command');

        $this->assertSame($command, $command->setKernel($this->createKernel()));
    }

    public function test_call_throws_when_kernel_not_set(): void
    {
        $this->expectException(\LogicException::class);

        $this->createCommand()->call('other:command');
    }

    public function test_call_returns_exit_code_from_kernel(): void
    {
        $kernel = $this->createKernel();
        $kernel->method('handle')->willReturn(42);

        $command = $this->createCommand();
        $command->setName('test:command');
        $command->setKernel($kernel);

        $this->assertSame(42, $command->call('other:command'));
    }
}
