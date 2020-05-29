<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class JobController
{
    /**
     * @Route("/jobs", name="jobs")
     */
    public function jobs()
    {
        return new Response(
            '<html><body>Jobs</body></html>'
        );
    }
}