<?php

namespace App\Service;

use App\Entity\Country;
use App\Repository\CountryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CountryService
{
    private $countryRepository;
    private $entityManager;

    public function __construct(CountryRepository $countryRepository, EntityManagerInterface $entityManager)
    {
        $this->countryRepository = $countryRepository;
        $this->entityManager = $entityManager;
    }

    //method to get all countries
    public function findAll(): array
    {
        return $this->countryRepository->findAll();
    }

    //method to get a country by id
    public function find(int $id): Country
    {
        $country = $this->countryRepository->find($id);

        if (!$country) {
            throw new NotFoundHttpException('Country not found.');
        }

        return $country;
    }

    //method to create a country
    public function create(string $requestData): Country
    {
        $requestData = json_decode($requestData, true);

        if (empty($requestData['name'])) {
            throw new BadRequestHttpException('Name is required.');
        }

        $country = new Country();
        $country->setName($requestData['name']);

        $this->entityManager->persist($country);
        $this->entityManager->flush();

        return $country;
    }

    //method to update a country
    public function update(int $id, string $requestData): Country
    {
        $requestData = json_decode($requestData, true);
        $country = $this->find($id);

        if (!empty($requestData['name'])) {
            $country->setName($requestData['name']);
        }

        $this->entityManager->flush();

        return $country;
    }

    //method to delete a country
    public function delete(int $id): void
    {
        $country = $this->find($id);

        if (!$country) {
            throw new NotFoundHttpException('Country not found.');
        }

        $this->entityManager->remove($country);
        $this->entityManager->flush();
    }

    //get country by name
    public function findByName(string $name): Country
    {
        $country = $this->countryRepository->findOneBy(['name' => $name]);

        if (!$country) {
            throw new NotFoundHttpException('Country not found.');
        }

        return $country;
    }
}
