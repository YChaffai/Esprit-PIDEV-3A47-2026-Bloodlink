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
    public function searchBy(array $criteria)
    {
        $qb = $this->createQueryBuilder('q')
            ->leftJoin('q.campagne', 'c');

        if (!empty($criteria['search'])) {
            $qb->andWhere('q.nom LIKE :kw OR q.prenom LIKE :kw')
               ->setParameter('kw', '%' . $criteria['search'] . '%');
        }

        if (!empty($criteria['campagne'])) {
            $qb->andWhere('q.campagne = :campagne')
               ->setParameter('campagne', $criteria['campagne']);
        }

        if (!empty($criteria['groupSanguin'])) {
            $qb->andWhere('q.group_sanguin = :gs')
               ->setParameter('gs', $criteria['groupSanguin']);
        }

        if (!empty($criteria['filter_date'])) {
            $qb->andWhere('q.date LIKE :d')
               ->setParameter('d', $criteria['filter_date']->format('Y-m-d') . '%');
        }

        if (!empty($criteria['filter_time'])) {
            $qb->andWhere('q.date LIKE :t')
               ->setParameter('t', '%' . $criteria['filter_time']->format('H:i') . '%');
        }
        
        // Sorting
        $sortField = 'q.date';
        $sortOrder = 'DESC';

        if (!empty($criteria['tri'])) {
            $parts = explode('_', $criteria['tri']);
            if (count($parts) === 2) {
                $type = $parts[0];
                $direction = $parts[1];
                
                if ($type === 'id') {
                    $sortField = 'q.id';
                }
                $sortOrder = $direction;
            }
        }
        
        $qb->orderBy($sortField, $sortOrder);

        return $qb->getQuery()->getResult();
    }
}
