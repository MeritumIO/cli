<?php

namespace Meritum\Cli\Test;

use Throwable;
use Georgeff\Kernel\Environment;
use Georgeff\Kernel\KernelException;
use Meritum\Cli\CliKernel;
use Meritum\Cli\CliKernelInterface;
use Meritum\Cli\CliKernelOption;
use Meritum\Cli\Command\CommandInterface;
use Meritum\Cli\ExitCode;
use Meritum\Cli\Exception\ExceptionHandlerInterface;
use Meritum\Cli\Output\SageStyleInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

final class CliKernelTest extends TestCase
{
    private function createKernel(): CliKernel
    {
        return new CliKernel(Environment::Testing);
    }

    private function createBootedKernel(): CliKernel
    {
        $kernel = $this->createKernel();
        $kernel->boot();

        return $kernel;
    }

    /**
     * @param string[] $aliases
     */
    private function createStubCommand(string $name, array $aliases = []): CommandInterface
    {
        return new class ($name, $aliases) implements CommandInterface {
            public function __construct(private string $name, private array $aliases) {}

            public function __invoke(SageStyleInterface $io): ExitCode { return ExitCode::Success; }
            public function getName(): string { return $this->name; }
            public function getDescription(): string { return ''; }
            public function getHelp(): string { return ''; }
            public function getAliases(): array { return $this->aliases; }
            public function isHidden(): bool { return false; }
            public function getArguments(): array { return []; }
            public function getOptions(): array { return []; }
            public function setKernel(CliKernelInterface $kernel): static { return $this; }
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

    public function test_find_returns_command_by_name(): void
    {
        $kernel = $this->createKernel();
        $target = $this->createStubCommand('test:command');
        $kernel->define('test.command', fn () => $target)->tag(CliKernelOption::CommandTag->value);
        $kernel->boot();

        $this->assertSame($target, $kernel->find('test:command'));
    }

    public function test_find_returns_command_by_alias(): void
    {
        $kernel = $this->createKernel();
        $target = $this->createStubCommand('test:command', ['tc']);
        $kernel->define('test.command', fn () => $target)->tag(CliKernelOption::CommandTag->value);
        $kernel->boot();

        $this->assertSame($target, $kernel->find('tc'));
    }

    public function test_find_returns_null_when_command_not_registered(): void
    {
        $kernel = $this->createBootedKernel();

        $this->assertNull($kernel->find('nonexistent'));
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

            public function handle(Throwable $e, SageStyleInterface $io): void
            {
                $this->caught = $e;
            }
        };

        $kernel = $this->createKernel();
        $kernel->define(ExceptionHandlerInterface::class, fn () => $handler)->share();
        $kernel->boot();

        $kernel->handle(new ArrayInput(['command' => 'nonexistent']), new NullOutput());

        $this->assertInstanceOf(CommandNotFoundException::class, $handler->caught);
    }

    public function test_handle_returns_one_when_exception_handler_invoked(): void
    {
        $kernel = $this->createKernel();
        $kernel->define(ExceptionHandlerInterface::class, fn () => new class implements ExceptionHandlerInterface {
            public function handle(Throwable $e, SageStyleInterface $io): void {}
        })->share();
        $kernel->boot();

        $exitCode = $kernel->handle(
            new ArrayInput(['command' => 'nonexistent']),
            new NullOutput()
        );

        $this->assertSame(1, $exitCode);
    }
}
