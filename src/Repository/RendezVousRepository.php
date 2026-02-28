<?php

namespace App\Repository;

use App\Entity\RendezVous;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RendezVous>
 */
class RendezVousRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RendezVous::class);
    }
    public function findByClientNotCancelled(int $client_id)
{
    return $this->createQueryBuilder('r')
        ->join('r.questionnaire', 'q')  // Join the questionnaire relation
        ->andWhere('q.client = :clientId')  // Reference the client through questionnaire
        ->andWhere('r.status != :status') 
        ->setParameter('clientId', $client_id)
        ->setParameter('status', 'annulé')
        ->getQuery()
        ->getResult();
}


//    /**
//     * @return RendezVous[] Returns an array of RendezVous objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?RendezVous
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    public function searchBy(array $criteria)
    {
        $qb = $this->createQueryBuilder('rv')
            ->leftJoin('rv.questionnaire', 'q')
            ->leftJoin('q.campagne', 'c')
            ->leftJoin('c.entites', 'e');

        if (!empty($criteria['search'])) {
            // Search in Questionnaire Nom/Prenom
            // Note: Questionnaire stores Nom/Prenom directly? Or access via Client->User?
            // Checking QuestionnaireController: $questionnaire->setNom($client->getUser()->getNom());
            // So Questionnaire has nom/prenom columns.
            $qb->andWhere('q.nom LIKE :kw OR q.prenom LIKE :kw')
               ->setParameter('kw', '%' . $criteria['search'] . '%');
        }

        if (!empty($criteria['campagne'])) {
            $qb->andWhere('q.campagne = :campagne')
               ->setParameter('campagne', $criteria['campagne']);
        }

        if (!empty($criteria['entite'])) {
            $qb->andWhere('e = :entite')
               ->setParameter('entite', $criteria['entite']);
        }

        if (!empty($criteria['status'])) {
            $qb->andWhere('rv.status = :status')
               ->setParameter('status', $criteria['status']);
        }

        if (!empty($criteria['filter_date'])) {
            $qb->andWhere('rv.date_don LIKE :d')
               ->setParameter('d', $criteria['filter_date']->format('Y-m-d') . '%');
        }

        if (!empty($criteria['filter_time'])) {
            $qb->andWhere('rv.date_don LIKE :t')
               ->setParameter('t', '%' . $criteria['filter_time']->format('H:i') . '%');
        }

        // Sorting
        $sortField = 'rv.date_don';
        $sortOrder = 'DESC';

        if (!empty($criteria['tri'])) {
            $parts = explode('_', $criteria['tri']);
            if (count($parts) === 2) {
                $type = $parts[0];
                $direction = $parts[1];

                if ($type === 'id') {
                    $sortField = 'rv.id';
                }
                $sortOrder = $direction;
            }
        }

        $qb->orderBy($sortField, $sortOrder);

        return $qb->getQuery()->getResult();
    }


    //lel client
  public function searchByClient(int $clientId, array $criteria = []): array
{
    $qb = $this->createQueryBuilder('rv')
        ->join('rv.questionnaire', 'q')
        ->where('q.client = :client_id')
        ->andWhere('rv.status != :status_annule')
        ->setParameter('client_id', $clientId)
        ->setParameter('status_annule', 'annulé');

    // Filtre Campagne
    if (!empty($criteria['campagne'])) {
        $qb->andWhere('q.campagne = :campagne')
           ->setParameter('campagne', $criteria['campagne']);
    }
   
    // Filtre Status (on utilise statusClient si c'est celui que vous affichez en front)
    $status = $criteria['statusClient'] ?? $criteria['status'] ?? null;
    if ($status) {
        $qb->andWhere('rv.status = :s')
           ->setParameter('s', $status);
    }

    // Filtre Date
    if (!empty($criteria['filter_date'])) {
        $qb->andWhere('rv.date_don LIKE :d')
           ->setParameter('d', $criteria['filter_date']->format('Y-m-d') . '%');
    }
    if (!empty($criteria['filter_time'])) {
        // format 'H:i' extrait "14:30". 
        // En SQL on cherche "%14:30%" dans "2026-02-15 14:30:00"
        $qb->andWhere('rv.date_don LIKE :t')
           ->setParameter('t', '%' . $criteria['filter_time']->format('H:i') . '%');
    }

    // Tri dynamique sur la date
    $direction = 'DESC';
    if (!empty($criteria['tri_date'])) {
        $direction = str_contains($criteria['tri_date'], 'ASC') ? 'ASC' : 'DESC';
    }
    $qb->orderBy('rv.date_don', $direction);

    return $qb->getQuery()->getResult();
}


public function findForExport(): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.questionnaire', 'q') // Jointure avec Questionnaire
            ->leftJoin('q.campagne', 'c') // Accès à Campagne via Questionnaire
            ->leftJoin('r.entite', 'e')   // Jointure avec Entite
            ->addSelect('q', 'c', 'e')     // Sélectionner aussi Questionnaire, Campagne et Entite
            ->where('r.date_don >= :today')
            ->setParameter('today', new \DateTime())
            ->orderBy('r.date_don', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
