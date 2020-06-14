<?php

namespace App\Command;

use App\Http\EmploiStoreHttp;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class HttpCommand extends Command
{
    protected static $defaultName = 'jobs:fetch';

    /** @var EmploiStoreHttp */
    private $emploiStoreHttp;

    public function __construct(EmploiStoreHttp $emploiStoreHttp)
    {
        $this->emploiStoreHttp = $emploiStoreHttp;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Fetch jobs from pôle emploi and store them in the database')
            ->setHelp('The command fetch jobs from pôle emploi and store them in the database. You must already have configured your API key to fetch pôle emploi API')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $jobs = $this->emploiStoreHttp->getJobs();

        dump($jobs);

        $output->write("Test d'écriture de notre commande. Premier appel!");

        return Command::SUCCESS;
    }
}
