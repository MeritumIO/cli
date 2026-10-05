<?php

namespace Meritum\Cli\Test;

use Throwable;
use Meritum\Cli\CliKernel;
use Meritum\Cli\ExitCode;
use Meritum\Cli\CliKernelInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Georgeff\Kernel\Environment\Testing;
use Meritum\Cli\Command\CommandInterface;
use Meritum\Cli\Output\SageStyleInterface;
use Georgeff\Kernel\Exception\KernelException;
use Meritum\Cli\Contract\ExceptionHandlerInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Meritum\Cli\Command\CommandDefinitionInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Exception\CommandNotFoundException;

final class CliKernelTest extends TestCase
{
    private function createKernel(): CliKernel
    {
        return new CliKernel(new Testing());
    }

    private function createBootedKernel(): CliKernel
    {
        $kernel = $this->createKernel();
        $kernel->boot();

        return $kernel;
    }

    private function createCommand(ExitCode $exitCode = ExitCode::Success): CommandInterface
    {
        return new class ($exitCode) implements CommandInterface {
            public int $invocations = 0;

            public function __construct(private readonly ExitCode $exitCode) {}

            public function __invoke(SageStyleInterface $io): ExitCode
            {
                $this->invocations++;

                return $this->exitCode;
            }
        };
    }

    public function test_implements_cli_kernel_interface(): void
    {
        $this->assertInstanceOf(CliKernelInterface::class, $this->createKernel());
    }

    public function test_default_name_is_sage(): void
    {
        $this->assertSame('Sage', $this->createKernel()->getName());
    }

    public function test_set_name_returns_static(): void
    {
        $kernel = $this->createKernel();

        $this->assertSame($kernel, $kernel->setName('TestApp'));
    }

    public function test_set_name_updates_name(): void
    {
        $kernel = $this->createKernel();
        $kernel->setName('TestApp');

        $this->assertSame('TestApp', $kernel->getName());
    }

    public function test_set_name_throws_after_boot(): void
    {
        $this->expectException(KernelException::class);

        $this->createBootedKernel()->setName('TestApp');
    }

    public function test_set_version_returns_static(): void
    {
        $kernel = $this->createKernel();

        $this->assertSame($kernel, $kernel->setVersion('2.0.0'));
    }

    public function test_set_version_updates_version(): void
    {
        $kernel = $this->createKernel();
        $kernel->setVersion('2.0.0');

        $this->assertSame('2.0.0', $kernel->getVersion());
    }

    public function test_set_version_throws_after_boot(): void
    {
        $this->expectException(KernelException::class);

        $this->createBootedKernel()->setVersion('2.0.0');
    }

    public function test_application_uses_kernel_name_and_version(): void
    {
        $kernel = $this->createKernel();
        $kernel->setName('TestApp')->setVersion('2.0.0');
        $kernel->boot();

        $output = new BufferedOutput();
        $kernel->handle(new ArrayInput(['--version' => true]), $output);

        $this->assertStringContainsString('TestApp 2.0.0', $output->fetch());
    }

    public function test_add_command_returns_command_definition(): void
    {
        $definition = $this->createKernel()->addCommand('test:command', 'test.command', fn () => $this->createCommand());

        $this->assertInstanceOf(CommandDefinitionInterface::class, $definition);
    }

    public function test_add_command_throws_on_duplicate_name(): void
    {
        $kernel = $this->createKernel();
        $kernel->addCommand('test:command', 'test.command', fn () => $this->createCommand());

        $this->expectException(KernelException::class);

        $kernel->addCommand('test:command', 'other.command', fn () => $this->createCommand());
    }

    public function test_add_command_throws_after_boot(): void
    {
        $this->expectException(KernelException::class);

        $this->createBootedKernel()->addCommand('test:command', 'test.command', fn () => $this->createCommand());
    }

    public function test_add_command_registers_factory_in_container(): void
    {
        $command = $this->createCommand();

        $kernel = $this->createKernel();
        $kernel->addCommand('test:command', 'test.command', fn () => $command);
        $kernel->boot();

        $this->assertSame($command, $kernel->getContainer()->get('test.command'));
    }

    public function test_command_factory_receives_container(): void
    {
        $received = null;

        $kernel = $this->createKernel();
        $kernel->addCommand('test:command', 'test.command', function (ContainerInterface $c) use (&$received) {
            $received = $c;

            return $this->createCommand();
        });
        $kernel->boot();

        $kernel->handle(new ArrayInput(['command' => 'test:command']), new NullOutput());

        $this->assertInstanceOf(ContainerInterface::class, $received);
    }

    public function test_commands_are_not_resolved_on_boot(): void
    {
        $resolved = false;

        $kernel = $this->createKernel();
        $kernel->addCommand('test:command', 'test.command', function () use (&$resolved) {
            $resolved = true;

            return $this->createCommand();
        });
        $kernel->boot();

        $this->assertFalse($resolved);
    }

    public function test_commands_are_not_resolved_when_listing(): void
    {
        $resolved = false;

        $kernel = $this->createKernel();
        $kernel->addCommand('test:command', 'test.command', function () use (&$resolved) {
            $resolved = true;

            return $this->createCommand();
        })->description('Listed without being resolved');
        $kernel->boot();

        $output = new BufferedOutput();
        $kernel->handle(new ArrayInput(['command' => 'list']), $output);

        $this->assertStringContainsString('test:command', $output->fetch());
        $this->assertFalse($resolved);
    }

    public function test_handle_resolves_only_the_executed_command(): void
    {
        $resolved = [];

        $kernel = $this->createKernel();
        $kernel->addCommand('first:command', 'first.command', function () use (&$resolved) {
            $resolved[] = 'first';

            return $this->createCommand();
        });
        $kernel->addCommand('second:command', 'second.command', function () use (&$resolved) {
            $resolved[] = 'second';

            return $this->createCommand();
        });
        $kernel->boot();

        $kernel->handle(new ArrayInput(['command' => 'second:command']), new NullOutput());

        $this->assertSame(['second'], $resolved);
    }

    public function test_handle_runs_command_by_name(): void
    {
        $command = $this->createCommand();

        $kernel = $this->createKernel();
        $kernel->addCommand('test:command', 'test.command', fn () => $command);
        $kernel->boot();

        $kernel->handle(new ArrayInput(['command' => 'test:command']), new NullOutput());

        $this->assertSame(1, $command->invocations);
    }

    public function test_handle_runs_command_by_alias(): void
    {
        $command = $this->createCommand();

        $kernel = $this->createKernel();
        $kernel->addCommand('test:command', 'test.command', fn () => $command)->alias('tc');
        $kernel->boot();

        $kernel->handle(new ArrayInput(['command' => 'tc']), new NullOutput());

        $this->assertSame(1, $command->invocations);
    }

    public function test_handle_returns_command_exit_code(): void
    {
        $kernel = $this->createKernel();
        $kernel->addCommand('test:command', 'test.command', fn () => $this->createCommand(ExitCode::Error));
        $kernel->boot();

        $exitCode = $kernel->handle(new ArrayInput(['command' => 'test:command']), new NullOutput());

        $this->assertSame(ExitCode::Error->value, $exitCode);
    }

    public function test_handle_can_be_called_multiple_times(): void
    {
        $command = $this->createCommand();

        $kernel = $this->createKernel();
        $kernel->addCommand('test:command', 'test.command', fn () => $command);
        $kernel->boot();

        $kernel->handle(new ArrayInput(['command' => 'test:command']), new NullOutput());
        $kernel->handle(new ArrayInput(['command' => 'test:command']), new NullOutput());

        $this->assertSame(2, $command->invocations);
    }

    public function test_handle_throws_when_not_booted(): void
    {
        $this->expectException(KernelException::class);

        $this->createKernel()->handle(new ArrayInput([]), new NullOutput());
    }

    public function test_handle_throws_when_shutdown(): void
    {
        $kernel = $this->createBootedKernel();
        $kernel->shutdown();

        $this->expectException(KernelException::class);

        $kernel->handle(new ArrayInput([]), new NullOutput());
    }

    public function test_handle_returns_zero_on_success(): void
    {
        $exitCode = $this->createBootedKernel()->handle(
            new ArrayInput(['command' => 'list']),
            new NullOutput()
        );

        $this->assertSame(0, $exitCode);
    }

    public function test_handle_rethrows_when_no_exception_handler_registered(): void
    {
        $this->expectException(CommandNotFoundException::class);

        $this->createBootedKernel()->handle(
            new ArrayInput(['command' => 'nonexistent']),
            new NullOutput()
        );
    }

    public function test_handle_invokes_exception_handler_on_throwable(): void
    {
        $handler = new class implements ExceptionHandlerInterface {
            public ?Throwable $caught = null;

            public function handle(Throwable $exception, SageStyleInterface $io): void
            {
                $this->caught = $exception;
            }
        };

        $kernel = $this->createKernel();
        $kernel->define(ExceptionHandlerInterface::class, fn () => $handler);
        $kernel->boot();

        $kernel->handle(new ArrayInput(['command' => 'nonexistent']), new NullOutput());

        $this->assertInstanceOf(CommandNotFoundException::class, $handler->caught);
    }

    public function test_handle_returns_one_when_exception_handler_invoked(): void
    {
        $kernel = $this->createKernel();
        $kernel->define(ExceptionHandlerInterface::class, fn () => new class implements ExceptionHandlerInterface {
            public function handle(Throwable $exception, SageStyleInterface $io): void {}
        });
        $kernel->boot();

        $exitCode = $kernel->handle(
            new ArrayInput(['command' => 'nonexistent']),
            new NullOutput()
        );

        $this->assertSame(ExitCode::Error->value, $exitCode);
    }
}
