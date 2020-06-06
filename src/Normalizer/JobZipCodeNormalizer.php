<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Exception\NormalizerException;
use App\Http\GeoGouvHttp;
use Spatie\Regex\Regex;
use function Symfony\Component\String\u;

class JobZipCodeNormalizer
{
    /** @var GeoGouvHttp */
    private $geoGouvHttp;

    public function __construct(GeoGouvHttp $geoGouvHttp)
    {
        $this->geoGouvHttp = $geoGouvHttp;
    }

    public function fromEmploiStoreResult(array $emploiStoreResult)
    {
        if (!array_key_exists('lieuTravail', $emploiStoreResult )) {
            return new NormalizerException('Key lieuTravail not found in emploi store result');
        }

        $lieuTravail = $emploiStoreResult['lieuTravail'];

        if (array_key_exists('codePostal', $lieuTravail)) {
            return $lieuTravail['codePostal'];
        }
        if (array_key_exists('commune', $lieuTravail)) {
            return $lieuTravail['commune'];
        }

        if (array_key_exists('libelle', $lieuTravail)) {
            $libelle = u($lieuTravail['libelle']);

            $splitedLibelle = $libelle->split('-');

            $rawDepartmentNumber = array_shift($splitedLibelle);
            if (!$rawDepartmentNumber !== null) {
                $departmentNumber = (string) $rawDepartmentNumber->trim();

                if (Regex::match('/^\d{2,5}$/', $departmentNumber)->hasMatch()) {
                    return $departmentNumber;
                }
            }
        }

        if (
            array_key_exists('latitude', $lieuTravail) &&
            array_key_exists('longitude', $lieuTravail)
        ) {
            $geoRawData = $this->geoGouvHttp->reverseFromLatLon($lieuTravail['latitude'], $lieuTravail['longitude']);

            if (array_key_exists('features', $geoRawData)) {
                $geoRawFirstElement = array_shift($geoRawData['features']);
                if (
                    !$geoRawFirstElement !== null &&
                    array_key_exists('properties', $geoRawFirstElement) &&
                    array_key_exists('postcode', $geoRawFirstElement['properties'])
                )
                {
                    return $geoRawFirstElement['properties']['postcode'];
                }
            }
        }

        return '';
    }
}