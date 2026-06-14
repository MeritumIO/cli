<?php

namespace Meritum\Cli\Test\Module;

use Georgeff\Kernel\Environment;
use Meritum\Cli\CliKernel;
use Meritum\Cli\CliKernelInterface;
use Meritum\Cli\CliKernelOption;
use Meritum\Cli\Command\CommandInterface;
use Meritum\Cli\ExitCode;
use Meritum\Cli\Output\SageStyleInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;

final class CliAppModuleTest extends TestCase
{
    private function createBootedKernel(): CliKernel
    {
        $kernel = new CliKernel(Environment::Testing);
        $kernel->boot();

        return $kernel;
    }

    private function getApp(CliKernel $kernel): Application
    {
        /** @var Application */
        return $kernel->getContainer()->get('cli.app');
    }

    public function test_registers_cli_app_as_symfony_application(): void
    {
        $this->assertInstanceOf(Application::class, $this->getApp($this->createBootedKernel()));
    }

    public function test_cli_app_uses_kernel_name(): void
    {
        $kernel = new CliKernel(Environment::Testing);
        $kernel->setName('TestApp');
        $kernel->boot();

        $this->assertSame('TestApp', $this->getApp($kernel)->getName());
    }

    public function test_cli_app_uses_kernel_version(): void
    {
        $kernel = new CliKernel(Environment::Testing);
        $kernel->setVersion('2.0.0');
        $kernel->boot();

        $this->assertSame('2.0.0', $this->getApp($kernel)->getVersion());
    }

    public function test_cli_app_auto_exit_is_disabled(): void
    {
        $this->assertFalse($this->getApp($this->createBootedKernel())->isAutoExitEnabled());
    }

    public function test_cli_app_catch_exceptions_is_disabled(): void
    {
        $this->assertFalse($this->getApp($this->createBootedKernel())->areExceptionsCaught());
    }

    public function test_tagged_commands_are_registered_in_cli_app(): void
    {
        $kernel = new CliKernel(Environment::Testing);
        $kernel->define('test.command', fn () => new class implements CommandInterface {
            public function __invoke(SageStyleInterface $io): ExitCode { return ExitCode::Success; }
            public function getName(): string { return 'test:command'; }
            public function getDescription(): string { return ''; }
            public function getHelp(): string { return ''; }
            public function getAliases(): array { return []; }
            public function isHidden(): bool { return false; }
            public function getArguments(): array { return []; }
            public function getOptions(): array { return []; }
            public function setKernel(CliKernelInterface $kernel): static { return $this; }
        })->tag(CliKernelOption::CommandTag->value);
        $kernel->boot();

        $this->assertTrue($this->getApp($kernel)->has('test:command'));
    }
}
