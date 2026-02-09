<?php

namespace App\Repository;

use App\Entity\DossierMed;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DossierMedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DossierMed::class);
    }

    /** @return DossierMed[] */
    public function findMineByClientId(int $clientId): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.client = :cid')
            ->setParameter('cid', $clientId)
            ->orderBy('d.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneMine(int $id, int $clientId): ?DossierMed
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.id = :id')
            ->andWhere('d.client = :cid')
            ->setParameter('id', $id)
            ->setParameter('cid', $clientId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
