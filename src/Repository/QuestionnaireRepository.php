<?php

namespace App\Repository;

use App\Entity\Questionnaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Questionnaire>
 */
class QuestionnaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Questionnaire::class);
    }

// src/Repository/QuestionnaireRepository.php

public function filterQuestionnaires(array $filters)
{
    $qb = $this->createQueryBuilder('q')
        // OPTIMISATION 1 : On "Select" la campagne tout de suite (Jointure Eager Loading).
        // Cela divise par 2 ou 3 le temps de chargement de la page (évite le problème N+1).
        ->leftJoin('q.campagne', 'c')
        ->addSelect('c') 
        ->orderBy('q.date', 'DESC');

    // --- 1. Recherche Globale (Nom, Prénom, Campagne) ---
    if (!empty($filters['search'])) {
        $term = trim($filters['search']); // On nettoie les espaces inutiles

        $qb->andWhere(
            $qb->expr()->orX(
                'q.nom LIKE :search',
                'q.prenom LIKE :search',
                'c.titre LIKE :search' // Cela devrait fonctionner si la jointure est faite
            )
        ) 
        ->setParameter('search', '%' . $term . '%');
    }

    // --- 2. Filtre Groupe Sanguin ---
    if (!empty($filters['groupSanguin'])) {
        $qb->andWhere('q.groupSanguin = :gs')
           ->setParameter('gs', $filters['groupSanguin']);
    }

    // --- 3. Filtre Date ---
    if (!empty($filters['filter_date'])) {
        $qb->andWhere('q.date LIKE :d')
           ->setParameter('d', $filters['filter_date']->format('Y-m-d') . '%');
    }

    // --- 4. Filtre Heure ---
    if (!empty($filters['filter_time'])) {
        $qb->andWhere('q.date LIKE :t')
           ->setParameter('t', '%' . $filters['filter_time']->format('H:i') . '%');
    }

    // --- 5. Tri ---
    if (!empty($filters['tri'])) {
        $parts = explode('_', $filters['tri']);
        if (count($parts) === 2) {
            $field = ($parts[0] === 'id') ? 'q.id' : 'q.date';
            $direction = $parts[1];
            $qb->orderBy($field, $direction);
        }
    }

    // OPTIMISATION 2 : Limiter les résultats !
    // C'est CRUCIAL pour la vitesse de la recherche "LIKE %...%".
    // 20 résultats suffisent pour une recherche instantanée (l'utilisateur affinera si besoin).
    $qb->setMaxResults(20);

    return $qb->getQuery()->getResult();
}
    //    /**
    //     * @return Questionnaire[] Returns an array of Questionnaire objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('q')
    //            ->andWhere('q.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('q.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Questionnaire
    //    {
    //        return $this->createQueryBuilder('q')
    //            ->andWhere('q.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
