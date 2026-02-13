<?php

namespace App\Repository;

use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commande>
 */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    public function searchBy(array $criteria)
    {
        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.id', 'DESC');

        if (!empty($criteria['search'])) {
            $qb->andWhere('c.type_sang LIKE :kw OR c.reference LIKE :kw') // Assuming reference exists, if not use ID
               ->setParameter('kw', '%' . $criteria['search'] . '%');
        }

        if (!empty($criteria['status'])) {
            $qb->andWhere('c.status LIKE :status')
               ->setParameter('status', $criteria['status'] . '%');
        }

        if (!empty($criteria['priority'])) {
            $qb->andWhere('c.priorite LIKE :priority')
               ->setParameter('priority', $criteria['priority'] . '%');
        }

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Commande[] Returns an array of Commande objects
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

    //    public function findOneBySomeField($value): ?Commande
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
