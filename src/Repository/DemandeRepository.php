<?php

namespace App\Repository;

use App\Entity\Demande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Demande>
 */
class DemandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Demande::class);
    }

    public function sortBy(string $field = 'id', string $direction = 'ASC')
    {
        return $this->createQueryBuilder('d')
            ->orderBy('d.' . $field, $direction)
            ->getQuery()
            ->getResult();
    }

    public function search(string $keyword)
    {
        return $this->createQueryBuilder('d')
            ->where('d.typeSang LIKE :kw')
            ->orWhere('d.status LIKE :kw')
            ->orWhere('d.urgence LIKE :kw')
            ->setParameter('kw', '%' . $keyword . '%')
            ->orderBy('d.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function searchBy(array $criteria)
    {
        $qb = $this->createQueryBuilder('d')
            ->leftJoin('d.banque', 'b')
            ->orderBy('d.id', 'DESC'); // Default sort

        if (!empty($criteria['search'])) {
            $qb->andWhere('d.typeSang LIKE :kw OR d.status LIKE :kw OR d.urgence LIKE :kw OR b.nom LIKE :kw')
               ->setParameter('kw', '%' . $criteria['search'] . '%');
        }

        if (!empty($criteria['urgence'])) {
            $qb->andWhere('d.urgence LIKE :urgence')
               ->setParameter('urgence', $criteria['urgence'] . '%');
        }

        if (!empty($criteria['status'])) {
            $qb->andWhere('d.status LIKE :status')
               ->setParameter('status', $criteria['status'] . '%');
        }

        return $qb->getQuery()->getResult();
    }
}
