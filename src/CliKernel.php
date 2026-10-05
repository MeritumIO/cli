<?php

namespace Meritum\Cli;

use Throwable;
use Georgeff\Kernel\Kernel;
use Composer\InstalledVersions;
use Meritum\Cli\Output\SageStyle;
use Meritum\Cli\Command\CommandDefinition;
use Symfony\Component\Console\Application;
use Meritum\Cli\Command\SymfonyCommandAdaptor;
use Georgeff\Kernel\Exception\KernelException;
use Symfony\Component\Console\Input\ArgvInput;
use Georgeff\Kernel\Contract\EnvironmentInterface;
use Meritum\Cli\Command\CommandDefinitionInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Input\InputInterface;
use Meritum\Cli\Contract\ExceptionHandlerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Georgeff\Kernel\Contract\ContainerBuilderInterface;

final class CliKernel extends Kernel implements CliKernelInterface
{
    private string $name;

    private string $version;

    /**
     * @var array<string, CommandDefinition>
     */
    private array $commands = [];

    private ?Application $app = null;

    public function __construct(EnvironmentInterface $environment, ?ContainerBuilderInterface $builder = null, bool $debug = false)
    {
        parent::__construct($environment, $builder, $debug);

        $this->name    = 'Sage';
        $this->version = $this->getDefaultVersion();
    }

    private function createApplication(): Application
    {
        $app = new Application($this->name, $this->version);

        $app->setAutoExit(false);
        $app->setCatchExceptions(false);

        foreach ($this->commands as $command) {
            $app->addCommand(new SymfonyCommandAdaptor($command, $this->getContainer()));
        }

        $this->commands = [];

        return $app;
    }

    private function getDefaultVersion(): string
    {
        $version = InstalledVersions::getVersion('meritum/cli') ?? 'dev';

        return (string) preg_replace('/\.0$/', '', $version);
    }

    public function setName(string $name): static
    {
        KernelException::throwIf($this->isBooted(), 'Kernel has already been booted, cannot set the name parameter');

        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setVersion(string $version): static
    {
        KernelException::throwIf($this->isBooted(), 'Kernel has already been booted, cannot set the version parameter');

        $this->version = $version;

        return $this;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function addCommand(string $name, string $id, callable $factory): CommandDefinitionInterface
    {
        KernelException::throwIf(isset($this->commands[$name]), "Command with name [$name] has already been registered");

        $this->define($id, $factory);

        return $this->commands[$name] = new CommandDefinition($name, $id);
    }

    public function handle(?InputInterface $input = null, ?OutputInterface $output = null): int
    {
        KernelException::throwIf($this->isShutdown(), 'Kernel is shutdown');

        KernelException::throwIf(!$this->isBooted(), 'Kernel is not booted, cannot call handle');

        if (null === $this->app) {
            $this->app = $this->createApplication();
        }

        $input  ??= new ArgvInput();
        $output ??= new ConsoleOutput();

        try {
            $exitCode = $this->app->run($input, $output);
        } catch (Throwable $e) {
            $handler = $this->getExceptionHandler();

            if (null === $handler) {
                throw $e;
            } else {
                $handler->handle($e, new SageStyle($input, $output));

                $exitCode = ExitCode::Error->value;
            }
        }

        return $exitCode;
    }

    public function run(): int
    {
        $this->boot();

        $status = $this->handle();

        $this->shutdown();

        return $status;
    }

    private function getExceptionHandler(): ?ExceptionHandlerInterface
    {
        return $this->getContainer()->has(ExceptionHandlerInterface::class)
            ? $this->getContainer()->get(ExceptionHandlerInterface::class)
            : null;
    }
}
