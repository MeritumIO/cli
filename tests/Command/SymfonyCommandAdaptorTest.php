<?php

namespace Meritum\Cli\Test\Command;

use Meritum\Cli\ExitCode;
use Meritum\Cli\Output\SageStyle;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Meritum\Cli\Command\CommandInterface;
use Meritum\Cli\Command\CommandDefinition;
use Meritum\Cli\Output\SageStyleInterface;
use Meritum\Cli\Command\SymfonyCommandAdaptor;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

final class SymfonyCommandAdaptorTest extends TestCase
{
    private function createDefinition(): CommandDefinition
    {
        return new CommandDefinition('test:command', 'test.command');
    }

    private function createContainer(mixed $service): ContainerInterface
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->with('test.command')->willReturn($service);

        return $container;
    }

    private function createCommand(ExitCode $exitCode = ExitCode::Success): CommandInterface
    {
        return new class ($exitCode) implements CommandInterface {
            public ?SageStyleInterface $io = null;

            public function __construct(private readonly ExitCode $exitCode) {}

            public function __invoke(SageStyleInterface $io): ExitCode
            {
                $this->io = $io;

                return $this->exitCode;
            }
        };
    }

    private function createAdaptor(CommandDefinition $definition): SymfonyCommandAdaptor
    {
        return new SymfonyCommandAdaptor($definition, $this->createStub(ContainerInterface::class));
    }

    public function test_configure_sets_name(): void
    {
        $this->assertSame('test:command', $this->createAdaptor($this->createDefinition())->getName());
    }

    public function test_configure_sets_description(): void
    {
        $adaptor = $this->createAdaptor($this->createDefinition()->description('Does something useful'));

        $this->assertSame('Does something useful', $adaptor->getDescription());
    }

    public function test_configure_sets_help(): void
    {
        $adaptor = $this->createAdaptor($this->createDefinition()->help('Extended help text'));

        $this->assertSame('Extended help text', $adaptor->getHelp());
    }

    public function test_configure_sets_aliases(): void
    {
        $adaptor = $this->createAdaptor($this->createDefinition()->alias('tc')->alias('tcommand'));

        $this->assertSame(['tc', 'tcommand'], $adaptor->getAliases());
    }

    public function test_configure_sets_hidden(): void
    {
        $adaptor = $this->createAdaptor($this->createDefinition()->hide());

        $this->assertTrue($adaptor->isHidden());
    }

    public function test_configure_sets_arguments(): void
    {
        $definition = $this->createDefinition();
        $definition->argument('target');

        $this->assertTrue($this->createAdaptor($definition)->getDefinition()->hasArgument('target'));
    }

    public function test_configure_sets_options(): void
    {
        $definition = $this->createDefinition();
        $definition->option('force')->shortcut('f');

        $this->assertTrue($this->createAdaptor($definition)->getDefinition()->hasOption('force'));
    }

    public function test_constructing_does_not_resolve_command(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->never())->method('get');

        new SymfonyCommandAdaptor($this->createDefinition(), $container);
    }

    public function test_execute_resolves_command_from_container_by_id(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
                  ->method('get')
                  ->with('test.command')
                  ->willReturn($this->createCommand());

        (new SymfonyCommandAdaptor($this->createDefinition(), $container))->run(new ArrayInput([]), new NullOutput());
    }

    public function test_execute_passes_sage_style_to_invoke(): void
    {
        $command = $this->createCommand();

        (new SymfonyCommandAdaptor($this->createDefinition(), $this->createContainer($command)))
            ->run(new ArrayInput([]), new NullOutput());

        $this->assertInstanceOf(SageStyle::class, $command->io);
    }

    public function test_execute_returns_exit_code_value_from_invoke(): void
    {
        $exitCode = (new SymfonyCommandAdaptor($this->createDefinition(), $this->createContainer($this->createCommand(ExitCode::Error))))
            ->run(new ArrayInput([]), new NullOutput());

        $this->assertSame(ExitCode::Error->value, $exitCode);
    }

    public function test_execute_throws_when_service_is_not_a_command(): void
    {
        $adaptor = new SymfonyCommandAdaptor($this->createDefinition(), $this->createContainer(new \stdClass()));

        $this->expectException(\RuntimeException::class);

        $adaptor->run(new ArrayInput([]), new NullOutput());
    }

    public function test_execute_exposes_arguments_and_options_to_command(): void
    {
        $definition = $this->createDefinition();
        $definition->argument('target');
        $definition->option('format')->required();

        $command = $this->createCommand();

        (new SymfonyCommandAdaptor($definition, $this->createContainer($command)))
            ->run(new ArrayInput(['target' => 'foo', '--format' => 'json']), new NullOutput());

        $this->assertSame('foo', $command->io?->argument('target'));
        $this->assertSame('json', $command->io?->option('format'));
    }
}
