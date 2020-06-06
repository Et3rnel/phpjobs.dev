<?php

namespace App\Controller;

use App\Http\EmploiStoreHttp;
use App\Normalizer\JobZipCodeNormalizer;
use GuzzleHttp\Exception\GuzzleException;
use HttpException;
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
     *
     * @param EmploiStoreHttp $emploiStoreHttp
     * @param JobZipCodeNormalizer $jobZipCodeNormalizer
     *
     * @return Response
     *
     * @throws GuzzleException
     * @throws HttpException
     */
    public function fetchNewJobs(
        EmploiStoreHttp $emploiStoreHttp,
        JobZipCodeNormalizer $jobZipCodeNormalizer
    )
    {
        $jobs = $emploiStoreHttp->getJobs();

        $jobsResult = $jobs['resultats'];
        foreach ($jobsResult as $job) {
            dump($job['lieuTravail']);
            dump($jobZipCodeNormalizer->fromEmploiStoreResult($job));
            dump('====================================================================');
        }

        die();

        return new Response('Jobs fetched');
    }
}