<?php

namespace App\Service;

use App\Entity\Country;
use App\Repository\CountryRepository;
use Doctrine\ORM\EntityManagerInterface;

class CountryService
{
    private $countryRepository;
    private $entityManager;

    public function __construct(CountryRepository $countryRepository, EntityManagerInterface $entityManager)
    {
        $this->countryRepository = $countryRepository;
        $this->entityManager = $entityManager;
    }

    //action to get all countries
    /**
     * @return Country[]
     */
    public function findAll(): array
    {
        return $this->countryRepository->findAll();
    }

    //action to get a country by id
    public function find(int $id): Country
    {
        $country = $this->countryRepository->find($id);

        if (!$country) {
            throw new \InvalidArgumentException('Country not found.');
        }

        return $country;
    }

    //action to create a country
    public function create(string $name): Country
    {
        $country = new Country();
        $country->setName($name);

        $this->entityManager->persist($country);
        $this->entityManager->flush();

        return $country;
    }

    //action to update a country
    public function update(int $id, string $name): Country
    {
        $country = $this->countryRepository->find($id);

        if (!$country) {
            throw new \InvalidArgumentException('Country not found.');
        }

        $country->setName($name);

        $this->entityManager->flush();

        return $country;
    }

    //action to delete a country and all its cities
    public function delete(int $id): void
    {
        $country = $this->countryRepository->find($id);

        if (!$country) {
            throw new \InvalidArgumentException('Country not found.');
        }

        $this->entityManager->remove($country);
        $this->entityManager->flush();
    }
}
