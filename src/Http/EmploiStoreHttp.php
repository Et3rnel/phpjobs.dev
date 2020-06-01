<?php

declare(strict_types=1);

namespace App\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use HttpException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class EmploiStoreHttp
{
    /** @var Client */
    private $client;

    /** @var string */
    private $emploiStoreClientId;

    /** @var string */
    private $emploiStoreClientSecret;

    /** @var string */
    private $accessToken;

    public function __construct(string $emploiStoreClientId, string $emploiStoreClientSecret)
    {
        $this->emploiStoreClientId = $emploiStoreClientId;
        $this->emploiStoreClientSecret = $emploiStoreClientSecret;

        $createAccessTokenGeneratorStack = $this->createAccessTokenGeneratorStack();

        $this->client = new Client([
            'base_uri' => 'https://api.emploi-store.fr/partenaire/offresdemploi/',
            'headers' => [
                'accept' => 'application/json',
            ],
            'verify' => 'C:\Projects\udemy\private\cacert.pem', // TODO : check how to make this way better
            'http_errors' => false, // TODO : check if it's really necessary to set it false or if we can handle errors
            'handler' => $createAccessTokenGeneratorStack,
        ]);
    }

    /**
     * Returns all jobs as a pure response
     *
     * @return ResponseInterface
     *
     * @throws GuzzleException
     */
    public function getJobs(): ResponseInterface
    {
        return $this->client->request('GET', 'v2/offres/search');
    }

    /**
     * Fetch the access token from pole emploi API with credentials to be able to access offres d'emploi API
     *
     * @return string|null Returns the access token or null if the request failed
     *
     * @throws GuzzleException
     */
    private function fetchAccessToken(): ?string
    {
        $scope1 = "application_{$this->emploiStoreClientId}";
        $scope2 = 'api_offresdemploiv2';
        $scope3 = 'o2dsoffre';

        $client = new Client([
            'base_uri' => 'https://entreprise.pole-emploi.fr/',
            'verify' => 'C:\Projects\udemy\private\cacert.pem', // TODO : check how to make this way better
            'http_errors' => false, // TODO : check if it's really necessary to set it false or if we can handle errors
        ]);

        $response = $client->request('POST', 'connexion/oauth2/access_token', [
            'query' => [
                'realm' => 'partenaire',
            ],
            'form_params' => [
                'grant_type' =>	'client_credentials',
                'client_id' => $this->emploiStoreClientId,
                'client_secret' => $this->emploiStoreClientSecret,
                'scope'	=> "$scope1 $scope2 $scope3"
            ]
        ]);

        if ($response->getStatusCode() === Response::HTTP_OK) {
            $body = (string) $response->getBody();
            $bodyArray = json_decode($body, true);

            return $bodyArray['access_token'];
        }

        return null;
    }

    /**
     * Adds a middleware to Guzzle client to be able to fetch an access token on each request if the access token
     * is not set
     *
     * @return HandlerStack
     *
     * @throws GuzzleException
     * @throws HttpException
     */
    private function createAccessTokenGeneratorStack(): HandlerStack
    {
        if (!$this->accessToken) {
            $accessToken = $this->fetchAccessToken();
            if (!$accessToken) {
                throw new HttpException('Error while fetching the pole emlpoi access token');
            }

            $this->accessToken = $accessToken;
        }

        $stack = new HandlerStack();
        $stack->setHandler(new CurlHandler());
        $stack->push(Middleware::mapRequest(function (RequestInterface $request) {
            return $request->withHeader('Authorization', "Bearer {$this->accessToken}");
        }));

        return $stack;
    }
}