<?php

namespace App\AppCommands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

#[AsCommand(name: 'app:execute-curl')]
class ExecuteCurlCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setDescription('Executes a CURL request with specified paths.')
            ->addArgument('path1', InputArgument::REQUIRED, 'Path to the first file.')
            ->addArgument('path2', InputArgument::REQUIRED, description: 'Path to the second file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path1 = $input->getArgument(name: 'path1');
        $path2 = $input->getArgument('path2');

        $output->writeln('<info>Executing CURL request...</info>');

        $curlCommand = sprintf(
            'curl -X POST http://127.0.0.1:8000/process-files-fourier -H "Content-Type: application/json" -d \'{"path1": "%s", "path2": "%s"}\'',
            $path1,
            $path2
        );

        $process = Process::fromShellCommandline($curlCommand);
        $process->setTimeout(3600);
        $process->run();

        if (!$process->isSuccessful()) {
            $output->writeln('<error>CURL request failed:</error>');
            $output->writeln($process->getErrorOutput());
            return Command::FAILURE;
        }

        $output->writeln('<info>CURL request completed successfully:</info>');
        $output->writeln($process->getOutput());
        return Command::SUCCESS;
    }
}