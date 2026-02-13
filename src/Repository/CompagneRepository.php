<?php

namespace App\Repository;
use App\Entity\Client;
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

    public function findCompatibleForClient(Client $client): array
    {
        $qb = $this->createQueryBuilder('c');

        // 1. Filtre par Groupe Sanguin (Celui du client OU Universel 'Tous')
        $qb->where('c.typeSang LIKE :sang')
       ->orWhere('c.typeSang LIKE :tous')
       ->setParameter('sang', '%"' . $client->getTypeSang() . '"%')
       ->setParameter('tous', '%"Tous"%');

        // 2. Vérifier l'éligibilité temporelle (Délai de 3 semaines)
        if ($client->getDernierDon()) {
            // On clone la date pour ne pas modifier l'objet original du client
            $dateEligibilite = clone $client->getDernierDon();
            // On ajoute 3 semaines à la date du dernier don
            $dateEligibilite->modify('+3 weeks');
            
            // La compagne doit commencer APRÈS que le client soit redevenu éligible
            $qb->andWhere('c.date_debut >= :dateEligible')
               ->setParameter('dateEligible', $dateEligibilite);
        }

        // 3. Ne montrer que les compagnes qui ne sont pas encore terminées (date_fin >= aujourd'hui)
        $qb->andWhere('c.date_fin >= :today')
           ->setParameter('today', new \DateTime());

        return $qb->orderBy('c.date_debut', 'ASC')
                  ->getQuery()
                  ->getResult();
    }

}
