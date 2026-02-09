<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\DossierMed;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DossierMed>
 */
class DossierMedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DossierMed::class);
    }

    public function findByClientSearchAndSort(Client $client, ?string $search, string $sort, string $direction)
    {
        $qb = $this->createQueryBuilder('dm')
            ->leftJoin('dm.don', 'd')
            ->addSelect('d')
            ->andWhere('dm.client = :client')
            ->setParameter('client', $client);

        // --- SEARCH ---
        if ($search) {
            $qb->andWhere('d.typeDon LIKE :search OR dm.id LIKE :search OR dm.typeSang LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        // --- SORT ---
        switch ($sort) {
            case 'date':
                $qb->orderBy('d.date', $direction);
                break;
            case 'poids':
                $qb->orderBy('dm.poid', $direction);
                break;
            case 'type':
                $qb->orderBy('d.typeDon', $direction);
                break;
            // ✅ ADDED: Sort by Age
            case 'age':
                $qb->orderBy('dm.age', $direction);
                break;
            default:
                $qb->orderBy('d.date', 'DESC');
        }
        
        // Stability Sort: If values are equal, sort by ID to stop jumping
        $qb->addOrderBy('dm.id', 'DESC');

        return $qb->getQuery()->getResult();
    }
}