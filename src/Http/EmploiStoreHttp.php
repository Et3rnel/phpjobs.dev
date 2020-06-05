<?php

declare(strict_types=1);

namespace App\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use HttpException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class EmploiStoreHttp
{
    /** @var Client */
    private $client;

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
        $this->emploiStoreClientId = $emploiStoreClientId;
        $this->emploiStoreClientSecret = $emploiStoreClientSecret;
        $this->poleEmploiHttp = $poleEmploiHttp;

        $handlerStack = $poleEmploiHttp->createAccessTokenGeneratorStack();
        $handlerStack->push(
            Middleware::log(
                $logger,
                new MessageFormatter('{req_body} - {res_body}')
            )
        );

        $this->client = new Client([
            'base_uri' => 'https://api.emploi-store.fr/partenaire/offresdemploi/',
            'headers' => [
                'accept' => 'application/json',
            ],
            'verify' => 'C:\Projects\udemy\private\cacert.pem', // TODO : check how to make this way better
            'http_errors' => false, // TODO : check if it's really necessary to set it false or if we can handle errors
            'handler' => $handlerStack,
        ]);

    }

    /**
     * Returns all jobs as a pure response
     *
     * @return ResponseInterface
     *
     * @throws GuzzleException
     * @throws HttpException
     */
    public function getJobs(): array
    {
        $response = $this->client->request('GET', 'v2/offres/search', [
            'query' => [
                'codeROME' => 'M1805',
                'appellation' => '14156',
            ]
        ]);

        if ($response->getStatusCode() === Response::HTTP_PARTIAL_CONTENT) {
            return json_decode((string) $response->getBody(), true);
        }

        throw new HttpException();
    }
}