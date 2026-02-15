<?php

namespace App\Repository;

use App\Entity\Compagne;
use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Compagne>
 */
class CampagneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Compagne::class);
    }
public function findCompatibleForClient(Client $client): array
    {
        $qb = $this->createQueryBuilder('c');

        // 1. Filtre par Groupe Sanguin (Celui du client OU Universel 'Tous')
        // Puisque typeSang est un champ JSON, on utilise LIKE pour vérifier la présence du type
        $qb->where('c.typeSang LIKE :sang OR c.typeSang LIKE :tous')
           ->setParameter('sang', '%"' . $client->getTypeSang() . '"%')
           ->setParameter('tous', '%"Tous"%');

        // 2. Vérifier l'éligibilité temporelle (Délai de 3 semaines)
        if ($client->getDernierDon()) {
            // On clone la date pour ne pas modifier l'objet original du client
            $dateEligibilite = clone $client->getDernierDon();
            // On ajoute 3 semaines à la date du dernier don
            $dateEligibilite->modify('+3 weeks');
            
            // La campagne doit commencer APRÈS que le client soit redevenu éligible
            $qb->andWhere('c.date_debut >= :dateEligible')
               ->setParameter('dateEligible', $dateEligibilite);
        }

        // 3. Ne montrer que les campagnes qui ne sont pas encore terminées (date_fin >= aujourd'hui)
        $qb->andWhere('c.date_fin >= :today')
           ->setParameter('today', new \DateTime());

        return $qb->orderBy('c.date_debut', 'ASC')
                  ->getQuery()
                  ->getResult();
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
}
