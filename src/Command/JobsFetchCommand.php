<?php

namespace App\Command;

use App\Http\EmploiStoreHttp;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class JobsFetchCommand extends Command
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
            ->setDescription('Fetch jobs from pôle emploi API.')
            ->setHelp('Fetch jobs from pôle emploi API. You must configure the token before calling pôle emploi API')
            ->addArgument('limit_jobs_fetch', InputArgument::OPTIONAL, 'How many jobs you want to fetch ?')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limitJobsFetch = $input->getArgument('limit_jobs_fetch');
        if ($limitJobsFetch === null) {
            $limitJobsFetch = 100;
        } else {
            $limitJobsFetch = (int) $limitJobsFetch;
        }

        $jobs = $this->emploiStoreHttp->getJobs($limitJobsFetch);
        $jobsCount = count($jobs);

        $rows = [];
        foreach ($jobs as $job) {
            $intitule = array_key_exists('intitule', $job) ? $job['intitule'] : '';
            $typeContratLibelle = array_key_exists('typeContratLibelle', $job) ? $job['typeContratLibelle'] : '';
            $experienceLibelle = array_key_exists('experienceLibelle', $job) ? $job['experienceLibelle'] : '';

            $rows[] = [$intitule, $typeContratLibelle, $experienceLibelle];
        }

        $table = new Table($output);
        $table->setHeaders(['Intitule', 'Type de contrat', 'Experience require']);
        $table->setRows($rows);
        $table->setHeaderTitle("$jobsCount job(s) fetched from API");
        $table->setFooterTitle('New jobs fetched from API');
        $table->setStyle('box');
        $table->render();

        return Command::SUCCESS;
    }
}
