<?php

namespace App\Service;

use App\Entity\City;
use App\Entity\Country;
use App\Repository\CityRepository;
use App\Repository\CountryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CityService
{
    private $cityRepository;
    private $entityManager;
    private $countryService;
    private $countryRepository;

    public function __construct(CountryRepository $countryRepository, CityRepository $cityRepository, EntityManagerInterface $entityManager, CountryService $countryService)
    {
        $this->cityRepository = $cityRepository;
        $this->entityManager = $entityManager;
        $this->countryService = $countryService;
        $this->countryRepository = $countryRepository;
    }

    // function to get all cities
    public function findAll(): array
    {
        return $this->cityRepository->findAll();
    }

    // function to get a city by id
    public function find(int $id): City
    {
        $city = $this->cityRepository->find($id);

        if (!$city) {
            throw new NotFoundHttpException('City not found.');
        }

        return $city;
    }

    // function to create a new city
    public function create(string $requestData): City
    {
        $requestData = json_decode($requestData, true);

        if (empty($requestData['name'])) {
            throw new BadRequestHttpException('Name is required.');
        }

        $city = new City();
        $city->setName($requestData['name']);

        $countryId = $requestData['country_id'];
        $country = $this->getCountryById($countryId);

        $country->addCity($city);

        $this->entityManager->persist($city);
        $this->entityManager->flush();

        return $city;
    }

    // function to get a country by id
    public function getCountryById(int $countryId): ?Country
    {

        return $this->countryService->find($countryId);
    }

    // function to update a city
    public function update(int $id, string $requestData): City
    {
        $requestData = json_decode($requestData, true);
        $city = $this->find($id);

        if (!empty($requestData['name'])) {
            $city->setName($requestData['name']);
        }

        if (isset($requestData['country_id'])) {
            $countryId = $requestData['country_id'];
            $country = $this->getCountryById($countryId);
            $country->addCity($city);
        }

        $this->entityManager->flush();

        return $city;
    }

// function to delete a city
    public function delete(int $id): void
    {
        $city = $this->find($id);

        $this->entityManager->remove($city);
        $this->entityManager->flush();
    }

}