<?php

declare(strict_types=1);

namespace App\Http;

use App\Exception\VicopoHttpException;
use App\Http\Client\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class VicopoPostCodeToCityHttp extends AbstractHttp
{
    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);

        $this->createClient([
            'base_uri' => 'https://vicopo.selfbuild.fr/',
        ]);
    }

    /**
     * Returns the first city found from post code
     *
     * @param $postCode
     * @return mixed
     *
     * @throws VicopoHttpException
     * @throws GuzzleException
     */
    public function cityByPostCode($postCode): ?array
    {
        $response = $this->client->request('GET', '', [
            'query' => [
                'code' => $postCode,
            ]
        ]);

        if ($response->getStatusCode() === Response::HTTP_OK) {
            $response = json_decode((string) $response->getBody(), true);
            $cities = $response['cities'];
            array_shift($cities);
        }

        throw new VicopoHttpException();
    }
}