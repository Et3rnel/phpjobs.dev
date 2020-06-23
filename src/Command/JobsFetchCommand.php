<?php

namespace App\Command;

use App\Assembler\JobAssembler;
use App\Http\EmploiStoreHttp;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function Symfony\Component\String\s;

class JobsFetchCommand extends Command
{
    protected static $defaultName = 'jobs:fetch';

    /** @var EmploiStoreHttp */
    private $emploiStoreHttp;

    /** @var JobAssembler */
    private $jobAssembler;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var TagRepository */
    private $tagRepository;

    public function __construct(
        EmploiStoreHttp $emploiStoreHttp,
        JobAssembler $jobAssembler,
        EntityManagerInterface $entityManager,
        TagRepository $tagRepository
    )
    {
        $this->emploiStoreHttp = $emploiStoreHttp;
        $this->jobAssembler = $jobAssembler;
        $this->entityManager = $entityManager;
        $this->tagRepository = $tagRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Fetch jobs from pôle emploi API.')
            ->setHelp('Fetch jobs from pôle emploi API. You must configure the token before calling pôle emploi API')
            ->addArgument('limit_jobs_fetch', InputArgument::OPTIONAL, 'How many jobs you want to fetch ?')
            ->addOption('info', 'i', InputOption::VALUE_NONE, 'Do you want to display jobs fetched ?')
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

        $displayInfo = $input->getOption('info');
        if ($displayInfo) {
            $table = new Table($output);
            $table->setHeaders(['Intitule', 'Type de contrat', 'Experience require']);
            $table->setRows($rows);
            $table->setHeaderTitle("$jobsCount job(s) fetched from API");
            $table->setFooterTitle('New jobs fetched from API');
            $table->setStyle('box');
            $table->render();
        }

        $tags = $this->tagRepository->findAll();

        $progressBar = new ProgressBar($output, $jobsCount);
        $progressBar->start();

        /** @var array $job */
        foreach ($jobs as $job) {
            $jobEntity = $this->jobAssembler->fromEmploiStoreResultToJob($job);
            $jobDescription = s($jobEntity->getDescription());

            foreach ($tags as $tag) {
                if ($jobDescription->ignoreCase()->containsAny($tag->getLabel())) {
                    $jobEntity->addTag($tag);
                }
            }

            $this->entityManager->persist($jobEntity);
            $progressBar->advance();
        }
        $progressBar->finish();
        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
