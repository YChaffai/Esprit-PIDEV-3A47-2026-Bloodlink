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
        ->addSelect('q') // Optimisation : évite des requêtes supplémentaires en Twig
        ->leftJoin('q.campagne', 'c')
        ->addSelect('c');

    // --- FILTRE DE SÉCURITÉ CRUCIAL ---
    // On force l'affichage uniquement pour le client concerné
    if (!empty($criteria['client_id'])) {
        $qb->andWhere('q.client = :client_id')
           ->setParameter('client_id', $criteria['client_id']);
    }

    // On ignore les rendez-vous annulés pour le front-office
    $qb->andWhere('rv.status != :status_annule')
       ->setParameter('status_annule', 'annulé');

    // --- FILTRES DYNAMIQUES ---
    if (!empty($criteria['search'])) {
        $qb->andWhere('q.nom LIKE :kw OR q.prenom LIKE :kw OR c.titre LIKE :kw')
           ->setParameter('kw', '%' . $criteria['search'] . '%');
    }

    if (!empty($criteria['campagne'])) {
        $qb->andWhere('q.campagne = :campagne')
           ->setParameter('campagne', $criteria['campagne']);
    }

    if (!empty($criteria['status'])) {
        $qb->andWhere('rv.status = :status')
           ->setParameter('status', $criteria['status']);
    }

    // Support du champ 'statusClient' si utilisé dans ton formulaire front
    if (!empty($criteria['statusClient'])) {
        $qb->andWhere('rv.status = :sc')
           ->setParameter('sc', $criteria['statusClient']);
    }

    if (!empty($criteria['filter_date'])) {
        $qb->andWhere('rv.date_don LIKE :d')
           ->setParameter('d', $criteria['filter_date']->format('Y-m-d') . '%');
    }

    if (!empty($criteria['filter_time'])) {
        $qb->andWhere('rv.date_don LIKE :t')
           ->setParameter('t', '%' . $criteria['filter_time']->format('H:i') . '%');
    }

    // --- LOGIQUE DE TRI ---
    $sortField = 'rv.date_don';
    $sortOrder = 'DESC';

    // Gestion du tri spécifique (tri_date ou tri général)
    $tri = $criteria['tri_date'] ?? $criteria['tri'] ?? null;

    if (!empty($tri)) {
        $parts = explode('_', $tri);
        if (count($parts) === 2) {
            $sortOrder = $parts[1]; // 'ASC' ou 'DESC'
            // Si le tri est sur l'ID
            if ($parts[0] === 'id') {
                $sortField = 'rv.id';
            }
        }
    }

    $qb->orderBy($sortField, $sortOrder);

    return $qb->getQuery()->getResult();
}
}
