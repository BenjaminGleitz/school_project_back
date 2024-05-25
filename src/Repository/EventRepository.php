<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\City;
use App\Entity\Category;
use App\Entity\Country;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Event>
 *
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    // event by participant
    public function findByParticipantQuery(UserInterface $participant): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere(':participant MEMBER OF e.participant')
            ->andWhere('e.status = :status')
            ->setParameter('participant', $participant)
            ->setParameter('status', 'OPEN')
            ->getQuery()
            ->getResult();
    }

    // event by Creator
    public function findByCreatorQuery(UserInterface $creator): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.creator = :creator')
            ->andWhere('e.status = :status')
            ->setParameter('creator', $creator)
            ->setParameter('status', 'OPEN')
            ->getQuery()
            ->getResult();
    }

    // event by city
    public function findByCityQuery(City $city): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.city = :city')
            ->andWhere('e.status = :status')
            ->setParameter('city', $city)
            ->setParameter('status', 'OPEN')
            ->getQuery()
            ->getResult();
    }

// Récupérer les événements en fonction des filtres
    public function findByFilters(Country $country, ?City $city, ?Category $category, ?\DateTimeImmutable $date): array
    {
        $queryBuilder = $this->createQueryBuilder('e')
        ->andWhere('e.status = :status')
        ->setParameter('status', 'OPEN');

        // Ajoutez la clause WHERE pour le pays si spécifié
        if ($country) {
            $queryBuilder->andWhere('e.country = :country')
                ->setParameter('country', $country);
        }

        // Ajoutez la clause WHERE pour la ville si spécifiée
        if ($city) {
            $queryBuilder->andWhere('e.city = :city')
                ->setParameter('city', $city);
        }

        // Ajoutez la clause WHERE pour la catégorie si spécifiée
        if ($category) {
            $queryBuilder->andWhere('e.category = :category')
                ->setParameter('category', $category);
        }

        // Ajoutez la clause WHERE pour la date minimale de début si spécifiée
        if ($date) {
            $queryBuilder->andWhere('e.start_at >= :start_at')
                ->setParameter('start_at', $date);
        }

        // Exécutez la requête pour récupérer les événements filtrés
        return $queryBuilder->getQuery()->getResult();
    }
}
