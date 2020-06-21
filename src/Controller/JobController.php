<?php

namespace App\Controller;

use App\Assembler\JobAssembler;
use App\Entity\Job;
use App\Http\EmploiStoreHttp;
use App\Repository\JobRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\GuzzleException;
use HttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\String\s;

class JobController extends AbstractController
{
    /**
     * @Route("/jobs", name="jobs")
     *
     * @param JobRepository $jobRepository
     *
     * @return Response
     */
    public function jobs(JobRepository $jobRepository)
    {
        $jobs = $jobRepository->findAllByLatest();

        return $this->render('jobs/jobs.html.twig', [
            'jobs' => $jobs,
        ]);
    }

    /**
     * @Route("/job/{job}", name="job_show", methods={"GET"})
     *
     * @param Job $job
     *
     * @return Response
     */
    public function show(Job $job)
    {
        return $this->render('jobs/job.html.twig', [
            'job' => $job
        ]);
    }

    /**
     * @Route("/jobs/fetch", name="jobs_fetch")
     *
     * @param EmploiStoreHttp $emploiStoreHttp
     * @param JobAssembler $jobAssembler
     * @param EntityManagerInterface $entityManager
     * @param TagRepository $tagRepository
     *
     * @return Response
     *
     * @throws GuzzleException
     * @throws HttpException
     */
    public function fetchNewJobs(
        EmploiStoreHttp $emploiStoreHttp,
        JobAssembler $jobAssembler,
        EntityManagerInterface $entityManager,
        TagRepository $tagRepository
    )
    {
        $jobs = $emploiStoreHttp->getJobs();
        $tags = $tagRepository->findAll();

        $jobsResult = $jobs['resultats'];
        foreach ($jobsResult as $job) {
            $jobEntity = $jobAssembler->fromEmploiStoreResultToJob($job);
            $jobDescription = s($jobEntity->getDescription());

            foreach ($tags as $tag) {
                if ($jobDescription->ignoreCase()->containsAny($tag->getLabel())) {
                    $jobEntity->addTag($tag);
                }
            }

            $entityManager->persist($jobEntity);
        }

        $entityManager->flush();

        return new Response('Jobs fetched');
    }
}