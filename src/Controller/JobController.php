<?php

namespace App\Controller;

use App\Http\EmploiStoreHttp;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class JobController extends AbstractController
{
    /**
     * @Route("/jobs", name="jobs")
     */
    public function jobs()
    {
        return $this->render('jobs/jobs.html.twig', [
            'jobs' => [
                'job1' => 'my_job_1',
                'job2' => 'my_job_2',
            ]
        ]);
    }

    /**
     * @Route("/job", name="create_job", methods={"POST"})
     */
    public function createJob()
    {
//        $job = new Job();
//        $job->setTitle('Developpeur PHP');

        return $this->redirect('jobs');
    }

    /**
     * @Route("/jobs/fetch", name="jobs_fetch")
     */
    public function fetchNewJobs(EmploiStoreHttp $emploiStoreHttp)
    {
        $a = $emploiStoreHttp->getJobs();

        return new Response('Ah');
    }
}