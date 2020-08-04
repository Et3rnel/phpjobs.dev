<?php

namespace App\Controller;

use App\Entity\Job;
use App\Repository\JobRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
}