<?php

namespace App\Service;

use App\Entity\Country;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CountryService
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getAllCountries(): array
    {
        $sql = "SELECT * FROM country";
        return $this->connection->fetchAllAssociative($sql);
    }

    public function getCountry(int $id): array
    {
        $sql = "SELECT * FROM country WHERE id = :id";
        $country = $this->connection->fetchAssociative($sql, ['id' => $id]);

        if (!$country) {
            throw new \InvalidArgumentException('Country not found.');
        }

        return $country;
    }

    public function createCountry(string $name): array
    {
        $sql = "INSERT INTO country (name) VALUES (:name)";
        $this->connection->executeStatement($sql, ['name' => $name]);

        $sql = "SELECT * FROM country WHERE name = :name";
        return $this->connection->fetchAssociative($sql, ['name' => $name]);
    }

    public function updateCountry(int $id, string $name): Country
    {
        $sql = "UPDATE country SET name = :name WHERE id = :id";
        $affectedRows = $this->connection->executeStatement($sql, ['id' => $id, 'name' => $name]);

        if ($affectedRows === 0) {
            throw new \InvalidArgumentException('Country not found.');
        }

        // Récupérer les données du pays après la mise à jour
        $updatedCountryData = $this->getCountry($id);

        // Construire un objet Country à partir des données récupérées
        $updatedCountry = new Country();
        $updatedCountry->setName($updatedCountryData['name']);

        return $updatedCountry;
    }

    public function deleteCountry(int $id): void
    {
        $sql = "DELETE FROM country WHERE id = :id";
        $country = $this->connection->executeStatement($sql, ['id' => $id]);

        if ($country === 0) {
            throw new \InvalidArgumentException('Country not found.');
        }
    }
}