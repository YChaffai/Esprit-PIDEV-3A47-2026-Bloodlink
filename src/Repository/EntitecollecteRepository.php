<?php

namespace App\Repository;

use App\Entity\Entitecollecte;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Entitecollecte>
 */
class EntitecollecteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Entitecollecte::class);
    }


    //    /**
    //     * @return Entitecollecte[] Returns an array of Entitecollecte objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Entitecollecte
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findBySearchAndSort(?string $search, string $sort, string $direction): array
    {
        $qb = $this->createQueryBuilder('e');

        if ($search) {
            $qb->andWhere('e.nom LIKE :search OR e.localisation LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        $allowedSorts = ['id', 'nom', 'localisation', 'telephone'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'id';
        }

        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        return $qb->orderBy('e.' . $sort, $direction)
            ->getQuery()
            ->getResult();
    }
}
