<?php

namespace App\Service;

use App\Entity\City;
use App\Entity\Country;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;

class CityService
{
    private $cityRepository;
    private $entityManager;
    private $countryService;

    public function __construct(CityRepository $cityRepository, EntityManagerInterface $entityManager, CountryService $countryService)
    {
        $this->cityRepository = $cityRepository;
        $this->entityManager = $entityManager;
        $this->countryService = $countryService;
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
            throw new \InvalidArgumentException('City not found.');
        }

        return $city;
    }

    // function to create a new city
    public function create(string $name, int $countryId): City
    {
        $country = $this->getCountryById($countryId);
        if (!$country) {
            throw new \InvalidArgumentException('The specified country does not exist.');
        }

        $city = new City();
        $city->setName($name);
        $city->setCountry($country);

        $this->entityManager->persist($city);
        $this->entityManager->flush();

        return $city;
    }

    // function to update a city
    public function update(int $id, string $name, int $countryId): City
    {
        $city = $this->cityRepository->find($id);

        if (!$city) {
            throw new \InvalidArgumentException('City not found.');
        }

        $country = $this->getCountryById($countryId);

        $city->setName($name);
        $city->setCountry($country);

        $this->entityManager->persist($city);
        $this->entityManager->flush();

        return $city;
    }

    // function to delete a city
    public function delete(int $id): void
    {
        $city = $this->cityRepository->find($id);

        if (!$city) {
            throw new \InvalidArgumentException('City not found.');
        }

        $this->entityManager->remove($city);
        $this->entityManager->flush();
    }

    // function to get the country entity by ID
    public function getCountryById(int $countryId): ?Country
    {
        return $this->entityManager->getRepository(Country::class)->find($countryId);
    }
}
