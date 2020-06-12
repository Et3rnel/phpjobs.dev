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
     * @return array
     *
     * @throws EmploiStoreHttpException
     * @throws GuzzleException
     */
    public function getJobs(): array
    {
        $response = $this->client->request('GET', 'v2/offres/search', [
            'query' => [
                'range' => '0-99',
                'codeROME' => 'M1805',
                'appellation' => '14156',
            ]
        ]);

        if ($response->getStatusCode() === Response::HTTP_PARTIAL_CONTENT) {
            return json_decode((string) $response->getBody(), true);
        }

        throw new EmploiStoreHttpException($response->getStatusCode());
    }
}