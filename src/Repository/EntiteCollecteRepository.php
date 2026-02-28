<?php

namespace App\Repository;

use App\Entity\EntiteCollecte;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EntiteCollecte>
 */
class EntiteCollecteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EntiteCollecte::class);
    }

    public function findBySearchAndSort(?string $search, string $sort, string $direction): array
    {
        $qb = $this->createQueryBuilder('e');

        if ($search) {
            $qb->andWhere('e.nom LIKE :search OR e.ville LIKE :search OR e.adresse LIKE :search OR e.type LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        $allowedSorts = ['id', 'nom', 'telephone', 'type', 'adresse', 'ville'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'id';
        }

        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        return $qb->orderBy('e.' . $sort, $direction)
            ->getQuery()
            ->getResult();
    }
}
