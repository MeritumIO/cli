<?php

namespace Meritum\Cli\Module;

use Meritum\Cli\CliKernelOption;
use Meritum\Cli\CliKernelInterface;
use Georgeff\Kernel\KernelInterface;
use Meritum\Cli\Command\CommandInterface;
use Symfony\Component\Console\Application;
use Georgeff\Kernel\Module\ModuleInterface;
use Georgeff\Kernel\DI\TagRegistryInterface;
use Meritum\Cli\Command\SymfonyCommandAdaptor;
use Symfony\Component\Console\Command\Command;

/**
 * @internal
 */
final class CliAppModule implements ModuleInterface
{
    public function register(KernelInterface $kernel): void
    {
        assert($kernel instanceof CliKernelInterface);

        $kernel->define('cli.app', fn() => $this->getApplication($kernel));
    }

    private function getApplication(CliKernelInterface $kernel): Application
    {
        $app = new Application($kernel->getName(), $kernel->getVersion());

        $app->setAutoExit(false);
        $app->setCatchExceptions(false);

        $app->addCommands($this->resolveCommands(
            $kernel->getContainer()->get(TagRegistryInterface::class),
            $kernel
        ));

        return $app;
    }

    /**
     * @return Command[]
     */
    private function resolveCommands(TagRegistryInterface $tags, CliKernelInterface $kernel): array
    {
        /** @var CommandInterface[] $commands */
        $commands = $tags->getTagged(CliKernelOption::CommandTag->value);

        $return = [];

        foreach ($commands as $command) {
            $return[] = new SymfonyCommandAdaptor($command->setKernel($kernel));
        }

        return $return;
    }
}
