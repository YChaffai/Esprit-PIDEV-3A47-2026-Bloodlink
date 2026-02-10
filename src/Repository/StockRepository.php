<?php

namespace App\Repository;

use App\Entity\Banque;
use App\Entity\Stock;
use Doctrine\DBAL\LockMode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Stock>
 */
class StockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stock::class);
    }

    public function findAvailableForBanque(Banque $banque, string $typeSang, int $qty): ?Stock
    {
        // Keep ids consistent with the stock table (type_orgid stores banque.id)
        $banqueId = $banque->getId();

        return $this->createQueryBuilder('s')
            ->andWhere('s.type_sang = :ts')
            ->andWhere('s.type_org = :org')
            ->andWhere('s.type_orgid = :orgid')
            ->andWhere('s.quantite >= :qty')
            ->setParameter('ts', $typeSang)
            ->setParameter('org', 'banque') // matches validator/form
            ->setParameter('orgid', $banqueId)
            ->setParameter('qty', $qty)
            ->orderBy('s.quantite', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    //    /**
    //     * @return Stock[] Returns an array of Stock objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Stock
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
