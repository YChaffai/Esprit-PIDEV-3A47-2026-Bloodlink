<?php

namespace App\Repository;

use App\Entity\Compagne;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Compagne>
 */
class CompagneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Compagne::class);
    }

    //    /**
    //     * @return Compagne[] Returns an array of Compagne objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Compagne
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findBySearchAndSort(?string $search, string $sort, string $direction): array
    {
        $qb = $this->createQueryBuilder('c');

        if ($search) {
            $qb->andWhere('c.titre LIKE :search OR c.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        // Validate sort field to prevent SQL injection
        $allowedSorts = ['id', 'titre', 'date_debut', 'date_fin', 'created_at', 'updated_at'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'id';
        }

        // Validate direction
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        return $qb->orderBy('c.' . $sort, $direction)
            ->getQuery()
            ->getResult();
    }
}
