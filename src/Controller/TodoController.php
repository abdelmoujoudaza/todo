<?php

namespace App\Controller;

use App\Entity\Todo;
use App\Entity\User;
use App\Entity\UserTodo;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Component\HttpFoundation\Request;
use Omines\DataTablesBundle\Column\TextColumn;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Omines\DataTablesBundle\Adapter\Doctrine\ORM\SearchCriteriaProvider;

class TodoController extends AbstractController
{
    /**
     * @Route("/todo", name="todo")
     */
    public function index(Request $request, DataTableFactory $dataTableFactory): Response
    {
        $table = $dataTableFactory->create()
            ->add('title', TextColumn::class)
            ->add('name', TextColumn::class)
            ->add('createdAt', DateTimeColumn::class)
            ->createAdapter(ORMAdapter::class, [
                'entity' => Todo::class,
                'query'  => function (QueryBuilder $builder) {
                    $builder
                        ->select('todo')
                        ->addSelect('user.name')
                        ->from(Todo::class, 'todo')
                        ->innerJoin(UserTodo::class, 'userTodo', Join::WITH, 'userTodo.todo = todo.id')
                        ->innerJoin(User::class, 'user', Join::WITH, 'userTodo.user = user.id')
                        ->groupBy('todo');
                },
                'criteria' => [
                    function (QueryBuilder $builder) {
                        $builder->andWhere('userTodo.isOwner = :isOwner')
                            ->setParameter('isOwner', true);
                    },
                    new SearchCriteriaProvider(),
                ],
            ])
            ->handleRequest($request);

        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render('todo/index.html.twig', ['datatable' => $table]);
    }
}
