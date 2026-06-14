<?php

namespace Meritum\Cli\Test\Command;

use Meritum\Cli\CliKernelInterface;
use Meritum\Cli\Command\Argument;
use Meritum\Cli\Command\CommandInterface;
use Meritum\Cli\Command\Option;
use Meritum\Cli\Command\SymfonyCommandAdaptor;
use Meritum\Cli\ExitCode;
use Meritum\Cli\Output\SageStyle;
use Meritum\Cli\Output\SageStyleInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

final class SymfonyCommandAdaptorTest extends TestCase
{
    private function createCommand(array $overrides = []): CommandInterface
    {
        return new class($overrides) implements CommandInterface {
            public function __construct(private readonly array $overrides) {}

            public function __invoke(SageStyleInterface $io): ExitCode
            {
                return $this->overrides['exitCode'] ?? ExitCode::Success;
            }

            public function getName(): string { return $this->overrides['name'] ?? 'test:command'; }
            public function getDescription(): string { return $this->overrides['description'] ?? 'Test description'; }
            public function getHelp(): string { return $this->overrides['help'] ?? 'Test help'; }
            public function getAliases(): array { return $this->overrides['aliases'] ?? []; }
            public function isHidden(): bool { return $this->overrides['hidden'] ?? false; }
            public function getArguments(): array { return $this->overrides['arguments'] ?? []; }
            public function getOptions(): array { return $this->overrides['options'] ?? []; }
            public function setKernel(CliKernelInterface $kernel): static { return $this; }
        };
    }

    public function test_configure_sets_name(): void
    {
        $adaptor = new SymfonyCommandAdaptor($this->createCommand(['name' => 'foo:bar']));

        $this->assertSame('foo:bar', $adaptor->getName());
    }

    public function test_configure_sets_description(): void
    {
        $adaptor = new SymfonyCommandAdaptor($this->createCommand(['description' => 'Does something useful']));

        $this->assertSame('Does something useful', $adaptor->getDescription());
    }

    public function test_configure_sets_help(): void
    {
        $adaptor = new SymfonyCommandAdaptor($this->createCommand(['help' => 'Extended help text']));

        $this->assertSame('Extended help text', $adaptor->getHelp());
    }

    public function test_configure_sets_aliases(): void
    {
        $adaptor = new SymfonyCommandAdaptor($this->createCommand(['aliases' => ['fb', 'foobar']]));

        $this->assertSame(['fb', 'foobar'], $adaptor->getAliases());
    }

    public function test_configure_sets_hidden(): void
    {
        $adaptor = new SymfonyCommandAdaptor($this->createCommand(['hidden' => true]));

        $this->assertTrue($adaptor->isHidden());
    }

    public function test_configure_sets_arguments(): void
    {
        $adaptor = new SymfonyCommandAdaptor($this->createCommand([
            'arguments' => [Argument::make('target')],
        ]));

        $this->assertTrue($adaptor->getDefinition()->hasArgument('target'));
    }

    public function test_configure_sets_options(): void
    {
        $adaptor = new SymfonyCommandAdaptor($this->createCommand([
            'options' => [Option::make('force')->shortcut('f')],
        ]));

        $this->assertTrue($adaptor->getDefinition()->hasOption('force'));
    }

    public function test_execute_delegates_to_invoke(): void
    {
        $command = new class implements CommandInterface {
            public bool $invoked = false;

            public function __invoke(SageStyleInterface $io): ExitCode
            {
                $this->invoked = true;
                return ExitCode::Success;
            }

            public function getName(): string { return 'test:command'; }
            public function getDescription(): string { return ''; }
            public function getHelp(): string { return ''; }
            public function getAliases(): array { return []; }
            public function isHidden(): bool { return false; }
            public function getArguments(): array { return []; }
            public function getOptions(): array { return []; }
            public function setKernel(CliKernelInterface $kernel): static { return $this; }
        };

        (new SymfonyCommandAdaptor($command))->run(new ArrayInput([]), new NullOutput());

        $this->assertTrue($command->invoked);
    }

    public function test_execute_passes_sage_style_to_invoke(): void
    {
        $command = new class implements CommandInterface {
            public ?SageStyleInterface $receivedStyle = null;

            public function __invoke(SageStyleInterface $io): ExitCode
            {
                $this->receivedStyle = $io;
                return ExitCode::Success;
            }

            public function getName(): string { return 'test:command'; }
            public function getDescription(): string { return ''; }
            public function getHelp(): string { return ''; }
            public function getAliases(): array { return []; }
            public function isHidden(): bool { return false; }
            public function getArguments(): array { return []; }
            public function getOptions(): array { return []; }
            public function setKernel(CliKernelInterface $kernel): static { return $this; }
        };

        (new SymfonyCommandAdaptor($command))->run(new ArrayInput([]), new NullOutput());

        $this->assertInstanceOf(SageStyle::class, $command->receivedStyle);
    }

    public function test_execute_returns_exit_code_value_from_invoke(): void
    {
        $exitCode = (new SymfonyCommandAdaptor($this->createCommand(['exitCode' => ExitCode::Error])))
            ->run(new ArrayInput([]), new NullOutput());

        $this->assertSame(ExitCode::Error->value, $exitCode);
    }
}
