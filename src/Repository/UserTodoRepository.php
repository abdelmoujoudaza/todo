<?php

namespace App\Repository;

use App\Entity\UserTodo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserTodo|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserTodo|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserTodo[]    findAll()
 * @method UserTodo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserTodoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserTodo::class);
    }

    // /**
    //  * @return UserTodo[] Returns an array of UserTodo objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserTodo
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
