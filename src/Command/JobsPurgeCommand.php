<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class JobsPurgeCommand extends Command
{
    protected static $defaultName = 'jobs:purge';

    protected function configure()
    {
        $this
            ->setDescription('Purge jobs in the database.')
            ->setHelp('Purge jobs in the database. You can add additional information with the command arguments')
            ->addArgument('nb_days_threshold', InputArgument::REQUIRED, 'How many days for the job to be considered too old ?')
            ->addArgument('tags_list', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Which tags (framework) do you want to keep ?')
            ->addOption('jobs_limit', 'l', InputOption::VALUE_REQUIRED, 'If specified, only X job(s) will be deleted')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->write('Start of command execution');

        // TODO : command creation work in progress

        $output->write('End of command execution');

        return Command::SUCCESS;
    }
}
