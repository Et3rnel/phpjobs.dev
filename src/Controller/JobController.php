<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}