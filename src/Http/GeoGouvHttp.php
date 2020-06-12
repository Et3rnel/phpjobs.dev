<?php

declare(strict_types=1);

namespace App\Http;

use GuzzleHttp\Exception\GuzzleException;
use HttpException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class GeoGouvHttp extends AbstractHttp
{
    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);

        $this->createClient([
            'base_uri' => 'https://api-adresse.data.gouv.fr/',
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