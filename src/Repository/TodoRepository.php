<?php

namespace App\Repository;

use App\Entity\Todo;
use App\Entity\User;
use App\Entity\UserTodo;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Todo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Todo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Todo[]    findAll()
 * @method Todo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TodoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Todo::class);
    }

    public function checkHasPermission(Todo $todo, User $user)
    {
        return $this->createQueryBuilder('todo')
            ->select("userTodo")
            ->innerJoin(UserTodo::class, 'userTodo', Join::WITH, 'userTodo.todo = todo.id')
            ->innerJoin(User::class, 'user', Join::WITH, 'userTodo.user = user.id')
            ->andWhere('todo = :todo')
            ->setParameter('todo', $todo)
            ->andWhere('user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function checkIsOwner(Todo $todo, User $user)
    {
        return $this->createQueryBuilder('todo')
            ->select("userTodo")
            ->innerJoin(UserTodo::class, 'userTodo', Join::WITH, 'userTodo.todo = todo.id')
            ->innerJoin(User::class, 'user', Join::WITH, 'userTodo.user = user.id')
            ->where('userTodo.isOwner = :isOwner')
            ->setParameter('isOwner', true)
            ->andWhere('todo = :todo')
            ->setParameter('todo', $todo)
            ->andWhere('user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }

    // /**
    //  * @return Todo[] Returns an array of Todo objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Todo
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
