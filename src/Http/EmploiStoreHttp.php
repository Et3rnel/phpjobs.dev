<?php

declare(strict_types=1);

namespace App\Http;

use App\Exception\EmploiStoreHttpException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class EmploiStoreHttp extends AbstractHttp
{
    /** @var string */
    private $emploiStoreClientId;

    /** @var string */
    private $emploiStoreClientSecret;

    /** @var PoleEmploiHttp */
    private $poleEmploiHttp;

    public function __construct(
        string $emploiStoreClientId,
        string $emploiStoreClientSecret,
        PoleEmploiHttp $poleEmploiHttp,
        LoggerInterface $logger
    )
    {
        parent::__construct($logger);

        $this->emploiStoreClientId = $emploiStoreClientId;
        $this->emploiStoreClientSecret = $emploiStoreClientSecret;
        $this->poleEmploiHttp = $poleEmploiHttp;

        $handlerStack = $poleEmploiHttp->createAccessTokenGeneratorStack();

        $this->createClient([
            'base_uri' => 'https://api.emploi-store.fr/partenaire/offresdemploi/',
            'headers' => [
                'accept' => 'application/json',
            ],
            'handler' => $handlerStack,
        ]);
    }

    /**
     * Returns all jobs as a pure response
     *
     * @param int $limit
     *
     * @return array
     *
     * @throws EmploiStoreHttpException
     * @throws GuzzleException
     */
    public function getJobs(int $limit = 100): array
    {
        $maxRange = $limit - 1;
        $range = "0-{$maxRange}";

        $response = $this->client->request('GET', 'v2/offres/search', [
            'query' => [
                'range' => $range,
                'codeROME' => 'M1805',
                'appellation' => '14156',
            ]
        ]);

        if ($response->getStatusCode() === Response::HTTP_PARTIAL_CONTENT) {
            $responseBody = (string) $response->getBody();
            $bodyArray = json_decode($responseBody, true);
            return $bodyArray['resultats'];
        }

        throw new EmploiStoreHttpException($response->getStatusCode());
    }
}