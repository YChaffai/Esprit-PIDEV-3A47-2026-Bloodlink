<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\Don;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Don>
 */
class DonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Don::class);
    }

    /**
     * Search and Sort Dons for a specific client
     */
    public function findByClientSearchAndSort(?Client $client, ?string $search, string $sort, string $direction)
    {
        $qb = $this->createQueryBuilder('d')
            ->leftJoin('d.client', 'c')
            ->leftJoin('c.user', 'u')
            ->addSelect('c', 'u');

        if ($client) {
            $qb->andWhere('d.client = :client')
               ->setParameter('client', $client);
        }

        // --- SEARCH LOGIC ---
        if ($search) {
            $qb->andWhere('d.typeDon LIKE :search OR d.id LIKE :search OR d.quantite LIKE :search OR u.nom LIKE :search OR u.prenom LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        // --- SORT LOGIC ---
        // Verify valid sort keys to prevent SQL injection or errors
        $validSorts = ['date', 'quantite', 'typeDon'];
        if (!in_array($sort, $validSorts)) {
            $sort = 'date';
        }
        
        // Verify valid direction
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        $qb->orderBy('d.' . $sort, $direction);

        return $qb->getQuery()->getResult();
    }
}