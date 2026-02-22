<?php

namespace App\Repository;

use App\Entity\Compagne;

use App\Entity\Client;
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
    public function findBySearchAndSort(?string $search, string $sort, string $direction): \Doctrine\ORM\Query
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
            ->getQuery();
    }
    ////wajd's function
   public function findCompatibleForClient(Client $client): array
{
    $qb = $this->createQueryBuilder('c');

    // 1. Filtre par Groupe Sanguin (Celui du client OU Universel 'Tous')
    $qb->where('c.typeSang LIKE :sang')
       ->orWhere('c.typeSang LIKE :tous')
       ->setParameter('sang', '%"'.$client->getTypeSang().'"%') // Assuming type_sang is stored as a string (or a JSON string)
       ->setParameter('tous', '%"Tous"%');

    // 2. Vérifier l'éligibilité temporelle (Délai de 3 semaines)
    if ($client->getDernierDon()) {
        // Clone the date to avoid modifying the original client object
        $dateEligibilite = clone $client->getDernierDon();
        // Add 3 weeks to the last donation date
        $dateEligibilite->modify('+3 weeks');
        
        // The campaign must start AFTER the client becomes eligible
        $qb->andWhere('c.date_debut >= :dateEligible')
           ->setParameter('dateEligible', $dateEligibilite);
    }

    // 3. Only show campaigns that have not finished yet (date_fin >= today)
    $qb->andWhere('c.date_fin >= :today')
       ->setParameter('today', new \DateTime());

    return $qb->orderBy('c.date_debut', 'ASC')
              ->getQuery()
              ->getResult();
}

    
    
    public function getFinishedCampaignsWithDonorCount(): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.questionnaires', 'q')
            ->andWhere('c.date_fin < :now')
            ->setParameter('now', new \DateTime())
            ->groupBy('c.id')
            ->select('c.id, c.titre, c.date_debut, c.date_fin, COUNT(q.id) AS nb_donneurs');

        return $qb->getQuery()->getResult();
    }


}
