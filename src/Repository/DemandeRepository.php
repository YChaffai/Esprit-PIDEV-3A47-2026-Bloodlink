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
            ->getQuery()
            ->getResult();
    }
}
