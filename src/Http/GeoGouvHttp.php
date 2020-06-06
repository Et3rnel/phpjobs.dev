<?php

declare(strict_types=1);

namespace App\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use HttpException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class GeoGouvHttp
{
    /** @var Client */
    private $client;

    public function __construct(LoggerInterface $logger)
    {
        $handlerStack = HandlerStack::create();
        $handlerStack->push(
            Middleware::log(
                $logger,
                new MessageFormatter('{req_body} - {res_body}')
            )
        );

        $this->client = new Client([
            'base_uri' => 'https://api-adresse.data.gouv.fr/',
            'verify' => 'C:\Projects\udemy\private\cacert.pem', // TODO : check how to make this way better
            'http_errors' => false, // TODO : check if it's really necessary to set it false or if we can handle errors
            'handler' => $handlerStack,
        ]);
    }

    /**
     * Returns geolocation from lat lon coordinates
     *
     * @param string $lat
     * @param string $lon
     *
     * @return mixed
     *
     * @throws HttpException
     * @throws GuzzleException
     */
    public function reverseFromLatLon(float $lat, float $lon): array
    {
        $response = $this->client->request('GET', '/reverse/', [
            'query' => [
                'lon' => $lon,
                'lat' => $lat,
            ]
        ]);

        if ($response->getStatusCode() === Response::HTTP_OK) {
            return json_decode((string) $response->getBody(), true);
        }

        throw new HttpException("This status code wasn't expected : {$response->getStatusCode()}");
    }

}