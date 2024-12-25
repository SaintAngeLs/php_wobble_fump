<?php

namespace App\AppCommands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

#[AsCommand(name: 'app:run-tests')]
class RunTestsCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setDescription('Runs PHPUnit tests by executing "php bin/phpunit".');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Running PHPUnit tests...</info>');

        $process = new Process(['php', 'bin/phpunit']);
        $process->setTimeout(3600);

        // Start the process and capture output in real-time
        $process->run(function ($type, $buffer) use ($output) {
            if (Process::ERR === $type) {
                $output->writeln("<error>$buffer</error>");
            } else {
                $output->writeln($buffer);
            }
        });

        // Check process success status
        if (!$process->isSuccessful()) {
            $output->writeln('<error>Tests failed.</error>');
            return Command::FAILURE;
        }

        $output->writeln('<info>Tests completed successfully.</info>');
        return Command::SUCCESS;
    }
}
