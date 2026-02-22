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
    public function countTotal(): int
    {
        return $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
    public function countByStatus(string $status): int
    {
        return $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.status = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }
    public function countByTypeSang()
    {
        return $this->createQueryBuilder('t')
            ->join('t.stock', 's')
            ->select('s.type_sang as typeSang, COUNT(t.id) as total')
            ->groupBy('s.type_sang')
            ->getQuery()
            ->getResult();
    }
}
