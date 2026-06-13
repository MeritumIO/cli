<?php

namespace Meritum\Cli;

use Throwable;
use Georgeff\Kernel\Kernel;
use Composer\InstalledVersions;
use Georgeff\Kernel\Environment;
use Meritum\Cli\Output\SageStyle;
use Meritum\Cli\Module\CliAppModule;
use Georgeff\Kernel\KernelException;
use Georgeff\Kernel\ServiceRegistrar;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Input\InputInterface;
use Meritum\Cli\Exception\ExceptionHandlerInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CliKernel extends Kernel implements CliKernelInterface
{
    private string $name;

    private string $version;

    public function __construct(Environment $environment, ?ServiceRegistrar $registrar = null, bool $debug = false)
    {
        parent::__construct($environment, $registrar, $debug);

        $this->name    = 'Sage';
        $this->version = InstalledVersions::getVersion('meritum/cli') ?? 'dev';

        $this->addModule(new CliAppModule());
    }

    private function throwIf(bool $condition, string $message): void
    {
        if ($condition) {
            throw new KernelException($message);
        }
    }

    public function setName(string $name): static
    {
        $this->throwIf($this->isBooted(), 'Kernel has already been booted, cannot set the name parameter');

        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setVersion(string $version): static
    {
        $this->throwIf($this->isBooted(), 'Kernel has already been booted, cannot set the version parameter');

        $this->version = $version;

        return $this;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function handle(?InputInterface $input = null, ?OutputInterface $output = null): int
    {
        $this->throwIf($this->isShutdown(), 'Kernel is shutdown');

        $this->throwIf(!$this->isBooted(), 'Kernel is not booted, cannot call handle');

        $input  ??= new ArgvInput();
        $output ??= new ConsoleOutput();

        /** @var \Symfony\Component\Console\Application $app */
        $app = $this->getContainer()->get('cli.app');

        try {
            $exitCode = $app->run($input, $output);
        } catch (Throwable $e) {
            $handler = $this->getExceptionHandler();

            if (null === $handler) {
                throw $e;
            } else {
                $handler->handle($e, $input, new SageStyle($input, $output));

                $exitCode = ExitCode::Error->value;
            }
        }

        return $exitCode;
    }

    public function run(): int
    {
        return $this->handle();
    }

    private function getExceptionHandler(): ?ExceptionHandlerInterface
    {
        return $this->getContainer()->has(ExceptionHandlerInterface::class)
            ? $this->getContainer()->get(ExceptionHandlerInterface::class)
            : null;
    }
}
