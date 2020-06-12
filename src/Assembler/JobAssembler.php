<?php

declare(strict_types=1);

namespace App\Assembler;

use App\Entity\Job;
use App\Exception\NormalizerException;
use App\Http\GeoGouvHttp;
use App\Http\VicopoPostCodeToCityHttp;
use DateTime;
use Spatie\Regex\Regex;
use function Symfony\Component\String\u;

class JobAssembler
{
    /** @var GeoGouvHttp */
    private $geoGouvHttp;

    /** @var VicopoPostCodeToCityHttp */
    private $vicopoPostCodeToCityHttp;

    public function __construct(GeoGouvHttp $geoGouvHttp, VicopoPostCodeToCityHttp $vicopoPostCodeToCityHttp)
    {
        $this->geoGouvHttp = $geoGouvHttp;
        $this->vicopoPostCodeToCityHttp = $vicopoPostCodeToCityHttp;
    }

    public function fromEmploiStoreResultToJob(array $emploiStoreResult): Job
    {
        return (new Job())
            ->setTitle($emploiStoreResult['intitule'])
            ->setDescription($emploiStoreResult['description'])
            ->setZipCode($this->normalizeZipCodeFromEmploiStoreResult($emploiStoreResult))
            ->setCity($this->normalizeCityFromEmploiStoreResult($emploiStoreResult))
            ->setContractType($emploiStoreResult['typeContratLibelle'])
            ->setRequiredLevel($emploiStoreResult['experienceLibelle'])
            ->setCompany($this->normalizeCompanyFromEmploiStoreResult($emploiStoreResult))
            ->setUrl($this->normalizeUrlFromEmploiStoreResult($emploiStoreResult))
            ->setCreationDate($this->normalizeCreationDateFromEmploiStoreResult($emploiStoreResult));
    }

    private function normalizeZipCodeFromEmploiStoreResult(array $emploiStoreResult)
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
                    $geoRawFirstElement !== null &&
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

    private function normalizeCompanyFromEmploiStoreResult(array $emploiStoreResult)
    {
        if (!array_key_exists('origineOffre', $emploiStoreResult )) {
            return new NormalizerException('Key origineOffre not found in emploi store result');
        }
        $origineOffre = $emploiStoreResult['origineOffre'];

        if (array_key_exists('partenaires', $origineOffre)) {
            $partenairesData = array_shift($origineOffre['partenaires']);
            if (
                is_array($partenairesData) &&
                array_key_exists('nom', $partenairesData)
            ) {
                return $partenairesData['nom'];
            }
        }

        return 'Pôle Emploi';
    }

    private function normalizeUrlFromEmploiStoreResult(array $emploiStoreResult)
    {
        if (!array_key_exists('origineOffre', $emploiStoreResult )) {
            return new NormalizerException('Key origineOffre not found in emploi store result');
        }
        $origineOffre = $emploiStoreResult['origineOffre'];

        if (array_key_exists('partenaires', $origineOffre)) {
            $partenairesData = array_shift($origineOffre['partenaires']);
            if (
                is_array($partenairesData) &&
                array_key_exists('url', $partenairesData)
            ) {
                return $partenairesData['url'];
            }
        }

        return '';
    }

    private function normalizeCreationDateFromEmploiStoreResult(array $emploiStoreResult)
    {
        if (array_key_exists('dateActualisation', $emploiStoreResult )) {
            return DateTime::createFromFormat(DATE_ATOM, $emploiStoreResult['dateActualisation']);
        }

        return DateTime::createFromFormat(DATE_ATOM, $emploiStoreResult['dateCreation']);
    }

    private function normalizeCityFromEmploiStoreResult(array $emploiStoreResult)
    {
        if (!array_key_exists('lieuTravail', $emploiStoreResult )) {
            return new NormalizerException('Key lieuTravail not found in emploi store result');
        }

        $lieuTravail = $emploiStoreResult['lieuTravail'];

        if (array_key_exists('libelle', $lieuTravail)) {
            $libelle = u($lieuTravail['libelle']);
            $splitedLibelle = $libelle->split('-');

            // We pretend that we don't have a correct city if we split more than 2 elements
            if (count($splitedLibelle) === 2) {
                $rawCity = end($splitedLibelle);
                return (string) $rawCity->trim();
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
                    $geoRawFirstElement !== null &&
                    array_key_exists('properties', $geoRawFirstElement) &&
                    array_key_exists('city', $geoRawFirstElement['properties'])
                )
                {
                    return $geoRawFirstElement['properties']['city'];
                }
            }
        }

        if (array_key_exists('codePostal', $lieuTravail)) {
            $city = $this->vicopoPostCodeToCityHttp->cityByPostCode(['codePostal']);
            if ($city !== null) {
                return $city;
            }
        }
        if (array_key_exists('commune', $lieuTravail)) {
            $city = $this->vicopoPostCodeToCityHttp->cityByPostCode(['commune']);
            if ($city !== null) {
                return $city;
            }
        }

        return 'France';
    }
}