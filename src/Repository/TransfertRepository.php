<?php

namespace App\Repository;

use App\Entity\Transfert;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transfert>
 */
class TransfertRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transfert::class);
    }

    public function searchBy(array $criteria)
    {
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.demande', 'd')
            ->addSelect('d')
            ->orderBy('t.id', 'DESC');

        if (!empty($criteria['search'])) {
            $qb->andWhere('t.toOrg LIKE :kw OR t.status LIKE :kw OR t.fromOrg LIKE :kw')
               ->setParameter('kw', '%' . $criteria['search'] . '%');
        }

        if (!empty($criteria['status'])) {
            $qb->andWhere('t.status LIKE :status')
               ->setParameter('status', $criteria['status'] . '%');
        }

        return $qb->getQuery()->getResult();
    }
}
