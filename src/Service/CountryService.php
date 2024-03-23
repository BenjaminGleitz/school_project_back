<?php

namespace App\Service;

use Doctrine\DBAL\Connection;

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
        return $this->connection->fetchAssociative($sql, ['id' => $id]);
    }

    public function createCountry(string $name): array
    {
        $sql = "INSERT INTO country (name) VALUES (:name)";
        $this->connection->executeStatement($sql, ['name' => $name]);

        $sql = "SELECT * FROM country WHERE name = :name";
        return $this->connection->fetchAssociative($sql, ['name' => $name]);
    }

    public function updateCountry(int $id, string $name): void
    {
        $sql = "UPDATE country SET name = :name WHERE id = :id";
        $this->connection->executeStatement($sql, ['id' => $id, 'name' => $name]);
    }

    public function deleteCountry(int $id): void
    {
        $sql = "DELETE FROM country WHERE id = :id";
        $this->connection->executeStatement($sql, ['id' => $id]);
    }
}