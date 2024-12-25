# Symfony Custom Console Commands

## Overview

Symfony's Console component allows you to create custom commands to interact with your application through the command line. These commands can perform a variety of tasks, such as running tests, processing files, or generating reports.

This guide covers:
1. How to create and register commands in Symfony.
2. Differences in implementation across Symfony versions.
3. Examples of commands for common tasks.

For more details, refer to the official Symfony documentation:
- [Symfony 6.0 Console Commands Documentation](https://symfony.com/doc/6.0/console.html)
- [Symfony Current Console Commands Documentation](https://symfony.com/doc/current/console.html)

---

## How to Create a Custom Command

### Symfony 6.1 and latest

#### Step 1: Create the Command Class

Custom commands extend the `Command` class provided by Symfony. In Symfony 7.x and 6.x, you can use the `#[AsCommand]` attribute to register your command.

```php
// src/Command/MyCustomCommand.php
namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// Use the #[AsCommand] attribute to register the command
#[AsCommand(
    name: 'app:my-custom-command',
    description: 'This is a custom Symfony command.',
)]
class MyCustomCommand extends Command
{
    protected function configure(): void
    {
        $this->setHelp('This command performs a custom task...');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Executing custom command...</info>');
        // Add your command logic here

        return Command::SUCCESS;
    }
}
```

#### Step 2: Run the Command
After creating the command, run it using:
```bash
php bin/console app:my-custom-command
```

---

### Symfony 6.0 and Older

In older Symfony versions, you must explicitly register commands in the service container.

#### Step 1: Create the Command Class

Commands in Symfony 5.x and older define the command name using the `$defaultName` static property or in the `configure()` method.

```php
// src/Command/LegacyCommand.php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LegacyCommand extends Command
{
    protected static $defaultName = 'app:legacy-command';

    protected function configure(): void
    {
        $this->setDescription('A legacy Symfony command.')
             ->setHelp('This command performs a legacy task...');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Executing legacy command...</info>');
        // Add your command logic here

        return Command::SUCCESS;
    }
}
```

#### Step 2: Register the Command
Ensure the command is registered in your `services.yaml` file.

```yaml
services:
    App\Command\LegacyCommand:
        tags: ['console.command']
```

#### Step 3: Run the Command
Run the command using:
```bash
php bin/console app:legacy-command
```

---

## Advanced Usage

### Adding Arguments and Options
You can define arguments and options in the `configure()` method.

```php
protected function configure(): void
{
    $this
        ->addArgument('arg1', InputArgument::REQUIRED, 'The first argument')
        ->addOption('option1', 'o', InputOption::VALUE_NONE, 'An optional flag');
}
```

Retrieve them in the `execute()` method:
```php
protected function execute(InputInterface $input, OutputInterface $output): int
{
    $arg1 = $input->getArgument('arg1');
    $option1 = $input->getOption('option1');
    $output->writeln("Argument: $arg1, Option: " . ($option1 ? 'true' : 'false'));

    return Command::SUCCESS;
}
```

---

## Example Commands

### Executing a CURL Request
Command to perform a CURL operation:
```php
// src/Command/ExecuteCurlCommand.php
namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

#[AsCommand(name: 'app:execute-curl', description: 'Executes a CURL request with file paths.')]
class ExecuteCurlCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('path1', InputArgument::REQUIRED, 'Path to the first file.')
            ->addArgument('path2', InputArgument::REQUIRED, 'Path to the second file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path1 = $input->getArgument('path1');
        $path2 = $input->getArgument('path2');

        $curlCommand = sprintf(
            'curl -X POST http://127.0.0.1:8000/process-files-fourier -H "Content-Type: application/json" -d \'{"path1": "%s", "path2": "%s"}\'',
            $path1,
            $path2
        );

        $process = Process::fromShellCommandline($curlCommand);
        $process->run();

        if (!$process->isSuccessful()) {
            $output->writeln('<error>CURL request failed:</error>');
            $output->writeln($process->getErrorOutput());
            return Command::FAILURE;
        }

        $output->writeln('<info>Request completed:</info>');
        $output->writeln($process->getOutput());
        return Command::SUCCESS;
    }
}
```

---

## Notes

- Symfony 6.[> 1] and 7.x support the `#[AsCommand]` attribute for easier command registration.
- Symfony 5.x and older require commands to be registered in `services.yaml` or `services.xml`.
- Use `Process` for running shell commands within your Symfony commands.

---

## Running Commands in Production
Ensure environment variables `APP_ENV` and `APP_DEBUG` are set correctly for production:
```bash
APP_ENV=prod APP_DEBUG=0 php bin/console <command-name>
```