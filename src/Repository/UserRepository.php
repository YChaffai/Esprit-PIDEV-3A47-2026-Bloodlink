<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, User::class);
  }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
  /**
   * @return User[]
   */
  public function searchAndSort(?string $query, string $sortField = 'id', string $sortOrder = 'ASC'): array
  {
    $qb = $this->createQueryBuilder('u');

    if ($query) {
      $qb->andWhere('u.nom LIKE :query OR u.prenom LIKE :query OR u.email LIKE :query OR u.id = :idQuery')
        ->setParameter('query', '%' . $query . '%')
        ->setParameter('idQuery', (int)$query);
    }

    $allowedSortFields = ['id', 'nom', 'prenom', 'email', 'role'];
    if (!in_array($sortField, $allowedSortFields)) {
      $sortField = 'id';
    }

    $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';

    return $qb->orderBy('u.' . $sortField, $sortOrder)
      ->getQuery()
      ->getResult();
  }
}
