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

    public function __construct(CityRepository $cityRepository, EntityManagerInterface $entityManager)
    {
        $this->cityRepository = $cityRepository;
        $this->entityManager = $entityManager;
    }

    public function findAll(): array
    {
        return $this->cityRepository->findAll();
    }

    public function find(int $id): City
    {
        $city = $this->cityRepository->find($id);

        if (!$city) {
            throw new \InvalidArgumentException('City not found.');
        }

        return $city;
    }

    public function create(string $name, int $countryId): City
    {
        // Get the country entity based on the provided ID
        $country = $this->getCountryById($countryId);

        // Create a new city entity with the provided name and country
        $city = new City();
        $city->setName($name);
        $city->setCountry($country);

        // Persist and flush the city entity to the database
        $this->entityManager->persist($city);
        $this->entityManager->flush();

        // Return the created city entity
        return $city;
    }

    public function update(int $id, string $name, int $countryId): City
    {
        // Fetch the city entity based on the provided ID
        $city = $this->cityRepository->find($id);

        if (!$city) {
            throw new \InvalidArgumentException('City not found.');
        }

        // Get the country entity based on the provided ID
        $country = $this->getCountryById($countryId);

        // Update the city entity with the new name and country
        $city->setName($name);
        $city->setCountry($country);

        // Persist and flush the updated city entity to the database
        $this->entityManager->persist($city);
        $this->entityManager->flush();

        // Return the updated city entity
        return $city;
    }

    public function delete(int $id): void
    {
        // Fetch the city entity based on the provided ID
        $city = $this->cityRepository->find($id);

        if (!$city) {
            throw new \InvalidArgumentException('City not found.');
        }

        // Remove the city entity from the database
        $this->entityManager->remove($city);
        $this->entityManager->flush();
    }

    public function getCountryById(int $countryId): ?Country
    {
        // Fetch the country entity from the database based on the provided ID
        $country = $this->entityManager->getRepository(Country::class)->find($countryId);

        // Return the fetched country entity
        return $country;
    }
}
