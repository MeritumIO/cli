# Changelog

All notable changes to `meritum/cli` are documented here.

---

## [1.1.0] — 2026-07-17

### Added
- `CliKernelInterface::find(string $command): ?CommandInterface` — looks up a registered command by name or alias via the `cli.commands` tag, without requiring a full `Application` dispatch
- `CommandNotFoundException` — thrown by `Command::call()` when the target command cannot be found

### Changed
- `Command::call()` now resolves the target command via `CliKernelInterface::find()` and runs it directly through `SymfonyCommandAdaptor`, instead of re-dispatching through `CliKernelInterface::handle()` and the full `Application`

---

## [1.0.0] — 2026-06-14

### Added
- `CliKernel` implementing `CliKernelInterface`, extending `georgeff/kernel`'s `Kernel`
- `setName()` / `getName()`, `setVersion()` / `getVersion()` — configurable before boot, default name `Sage`
- `handle(?InputInterface $input, ?OutputInterface $output): int` — dispatches the underlying Symfony `Application`, requires the kernel to be booted and not shutdown
- `run(): int` — convenience wrapper around `handle()` for entry-point scripts
- `Command` / `CommandInterface` — base class for user-defined commands; configured via `setName()`, `setDescription()`, `setHelp()`, `addAlias()`, `shouldHide()`, `addArgument()`, `addOption()`; configuration is locked once a kernel is set
- `Command::call()` — invoke another registered command from within a command, discarding output by default
- `Argument` / `Option` — fluent builders for command arguments and options (required/optional, defaults, arrays, shortcuts, negatable flags, shell completion suggestions)
- `SymfonyCommandAdaptor` — internal adapter bridging `CommandInterface` to `Symfony\Component\Console\Command\Command`
- `CliKernelOption::CommandTag` — DI tag (`cli.commands`) used to register commands with the kernel container
- `CliAppModule` — internal module wiring the Symfony `Application` and resolving tagged commands
- `SageStyle` / `SageStyleInterface` — I/O helper exposing `argument()`, `arguments()`, `option()`, `options()`, plus standard Symfony console style output
- `ExceptionHandlerInterface` — optional hook for handling unhandled exceptions during dispatch; when registered, `handle()` returns `ExitCode::Error` on catch instead of propagating
- `ExitCode` enum — `Success` (`0`) and `Error` (`1`)
