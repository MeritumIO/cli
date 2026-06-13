<?php

namespace Meritum\Cli\Test;

use Throwable;
use Georgeff\Kernel\Environment;
use Georgeff\Kernel\KernelException;
use Meritum\Cli\CliKernel;
use Meritum\Cli\CliKernelInterface;
use Meritum\Cli\Exception\ExceptionHandlerInterface;
use Meritum\Cli\Output\SageStyleInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
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

            public function handle(Throwable $e, InputInterface $input, SageStyleInterface $output): void
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
            public function handle(Throwable $e, InputInterface $input, SageStyleInterface $output): void {}
        })->share();
        $kernel->boot();

        $exitCode = $kernel->handle(
            new ArrayInput(['command' => 'nonexistent']),
            new NullOutput()
        );

        $this->assertSame(1, $exitCode);
    }
}
