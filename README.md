# meritum/cli

Module-first console kernel (Sage) for the Meritum ecosystem. Wraps symfony/console with a clean bootstrapping API — constructor injection for dependencies, fluent builders for command definitions, and the same kernel lifecycle as `meritum/http`.

## Requirements

- PHP 8.4+
- georgeff/kernel ^1.6
- symfony/console ^8.1

## Installation

```bash
composer require meritum/cli
```

## Bootstrapping

Create an entry point (e.g. `bin/console`):

```php
#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use Georgeff\Kernel\Environment;
use Meritum\Cli\CliKernel;

$kernel = new CliKernel(Environment::Production);

// register modules and commands here

$kernel->boot();

exit($kernel->run());
```

### Customising the application name and version

```php
$kernel = new CliKernel(Environment::Production);
$kernel->setName('myapp');
$kernel->setVersion('1.0.0');
```

These must be set before `boot()`.

## Registering Commands

Commands are registered via the kernel's container using the `cli.commands` tag. The `CliKernelOption` enum holds the tag name:

```php
use Meritum\Cli\CliKernelOption;

$kernel->define(CreateUserCommand::class, function ($c) {
    $command = new CreateUserCommand($c->get(UserRepository::class));

    $command->setName('user:create')
            ->setDescription('Create a new user')
            ->addArgument('email')->description('The user email address')
            ->addOption('admin')->description('Grant admin privileges');

    return $command;
})->tag(CliKernelOption::CommandTag->value);
```

Commands are resolved from the container at dispatch time, so all dependencies are injected via the constructor.

## Writing Commands

Extend `Meritum\Cli\Command\Command` and implement `__invoke`:

```php
use Meritum\Cli\Command\Command;
use Meritum\Cli\ExitCode;
use Meritum\Cli\Output\SageStyleInterface;
use Symfony\Component\Console\Input\InputInterface;

final class CreateUserCommand extends Command
{
    public function __construct(private readonly UserRepository $users) {}

    public function __invoke(InputInterface $input, SageStyleInterface $output): ExitCode
    {
        $email = $input->getArgument('email');

        $this->users->create($email);

        $output->success("User {$email} created.");

        return ExitCode::Success;
    }
}
```

The constructor is reserved for real dependencies. All command metadata (name, description, arguments, options) is configured in the factory definition.

### Arguments

```php
$command->addArgument('name');                          // required by default
$command->addArgument('name')->optional();              // optional
$command->addArgument('name')->optional('default');     // optional with default
$command->addArgument('tags')->array();                 // required array
$command->addArgument('tags')->array()->optional();     // optional array

$command->addArgument('name')
        ->description('The target name')
        ->suggest('foo', 'bar');                        // shell completion hints
```

### Options

```php
$command->addOption('force');                           // boolean flag (--force)
$command->addOption('output')->required();              // requires a value (--output=path)
$command->addOption('output')->optional('/tmp');        // optional value with default
$command->addOption('tag')->required()->array();        // repeatable (--tag=foo --tag=bar)
$command->addOption('ansi')->negatable();               // --ansi / --no-ansi

$command->addOption('output')
        ->shortcut('o')                                 // -o shortcut
        ->description('Output path')
        ->suggest('/tmp', '/var/log');                  // shell completion hints
```

## Calling Commands from Commands

A command can invoke another command via `call()`. The kernel must be initialised (i.e. the command must be registered and the kernel booted) for this to work:

```php
public function __invoke(InputInterface $input, SageStyleInterface $output): ExitCode
{
    $this->call('cache:clear', $output);

    $this->call('cache:warm', $output, ['--env' => 'prod']);

    return ExitCode::Success;
}
```

If no output is passed, the sub-command's output is discarded. The return value is the sub-command's exit code.

## Exception Handling

Register an `ExceptionHandlerInterface` implementation to catch unhandled exceptions thrown during dispatch. Without one, exceptions propagate to the caller.

```php
use Meritum\Cli\Exception\ExceptionHandlerInterface;
use Meritum\Cli\Output\SageStyleInterface;
use Symfony\Component\Console\Input\InputInterface;

final class ConsoleExceptionHandler implements ExceptionHandlerInterface
{
    public function handle(\Throwable $e, InputInterface $input, SageStyleInterface $output): void
    {
        $output->error($e->getMessage());
    }
}
```

Register it in the kernel before boot:

```php
$kernel->define(ExceptionHandlerInterface::class, fn () => new ConsoleExceptionHandler())->share();
```

The kernel returns `ExitCode::Error` (`1`) when the exception handler is invoked.

## Kernel Lifecycle

`CliKernel` follows the standard `georgeff/kernel` lifecycle:

```php
$kernel->onBooting(function ($kernel) { /* before boot */ });
$kernel->onBooted(function ($kernel)  { /* after boot  */ });
$kernel->onShutdown(function ($kernel) { /* before shutdown */ });
```

`handle()` requires the kernel to be booted and not shutdown. `run()` calls `handle()` and must also be called on a live kernel.

## Modules

Commands and services can be packaged into reusable modules:

```php
use Georgeff\Kernel\KernelInterface;
use Georgeff\Kernel\Module\ModuleInterface;
use Meritum\Cli\CliKernelOption;

final class MigrationsModule implements ModuleInterface
{
    public function register(KernelInterface $kernel): void
    {
        $kernel->define(MigrateCommand::class, fn ($c) => (new MigrateCommand(
            $c->get(Database::class)
        ))->setName('migrate')->setDescription('Run pending migrations'))
        ->tag(CliKernelOption::CommandTag->value);
    }
}
```

```php
$kernel->addModule(new MigrationsModule());
```
