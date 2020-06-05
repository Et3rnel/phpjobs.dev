<?php

declare(strict_types=1);

namespace App\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use HttpException;
use Psr\Cache\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\ItemInterface;

class PoleEmploiHttp
{
    /** @var Client */
    private $client;

    /** @var string */
    private $emploiStoreClientId;

    /** @var string */
    private $emploiStoreClientSecret;

    public function __construct(
        string $emploiStoreClientId,
        string $emploiStoreClientSecret,
        LoggerInterface $logger
    )
    {
        $this->emploiStoreClientId = $emploiStoreClientId;
        $this->emploiStoreClientSecret = $emploiStoreClientSecret;

        $handlerStack = HandlerStack::create();
        $handlerStack->push(
            Middleware::log(
                $logger,
                new MessageFormatter('{req_body} - {res_body}')
            )
        );

        $this->client = new Client([
            'base_uri' => 'https://entreprise.pole-emploi.fr/',
            'verify' => 'C:\Projects\udemy\private\cacert.pem', // TODO : check how to make this way better
            'http_errors' => false, // TODO : check if it's really necessary to set it false or if we can handle errors
            'handler' => $handlerStack,
        ]);
    }

    /**
     * Fetch the access token from pole emploi API with credentials to be able to access offres d'emploi API
     *
     * @return array|null Returns the access token information or null if the request failed
     *
     * @throws GuzzleException
     */
    private function fetchAccessToken(): ?array
    {
        $scope1 = "application_{$this->emploiStoreClientId}";
        $scope2 = 'api_offresdemploiv2';
        $scope3 = 'o2dsoffre';

        $response = $this->client->request('POST', 'connexion/oauth2/access_token', [
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

            return json_decode($body, true);
        }

        return null;
    }

    /**
     * Adds a middleware to Guzzle client to be able to fetch an access token on each request if the access token
     * is not set
     *
     * @return HandlerStack
     *
     * @throws InvalidArgumentException
     */
    public function createAccessTokenGeneratorStack(): HandlerStack
    {
        $cache = new FilesystemAdapter();

        $accessToken = $cache->get('pole_emploi_access_token', function (ItemInterface $item) {
            $accessTokenData = $this->fetchAccessToken();
            if ($accessTokenData === null) {
                throw new HttpException('Error while fetching the pole emploi access token');
            }

            $item->expiresAfter($accessTokenData['expires_in']);

            return $accessTokenData['access_token'];
        });

        $stack = HandlerStack::create();
        $stack->setHandler(new CurlHandler());
        $stack->push(Middleware::mapRequest(function (RequestInterface $request) use ($accessToken) {
            return $request->withHeader('Authorization', "Bearer {$accessToken}");
        }));

        return $stack;
    }
}