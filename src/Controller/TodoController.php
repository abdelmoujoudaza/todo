<?php

namespace App\Controller;

use DateTime;
use App\Entity\Task;
use App\Entity\Todo;
use App\Entity\User;
use Doctrine\ORM\Query;
use App\Entity\UserTodo;
use App\Form\TaskFormType;
use App\Form\TodoFormType;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Component\HttpFoundation\Request;
use Omines\DataTablesBundle\Column\BoolColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\Column\TwigColumn;
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
    public function index(Request $request, DataTableFactory $dataTableFactory, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $todo = new Todo();
        $form = $this->createForm(TodoFormType::class, $todo)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $now  = new DateTime();

            $todo->setTitle($form->get('title')->getData())
                ->setCreatedAt($now)
                ->setUpdatedAt($now);

            $entityManager->persist($todo);

            $userTodo = new UserTodo();

            $userTodo->setUser($user)
                ->setTodo($todo)
                ->setIsOwner(true);

            $entityManager->persist($userTodo);

            $entityManager->flush();

            $this->addFlash('success', 'Votre liste de tâches a été créée avec succès.');

            return $this->redirectToRoute('todo_show', ['id' => $todo->getId()]);
        }

        $table = $dataTableFactory->create()
            ->add('title', TwigColumn::class, [
                'label' => 'TODO LIST',
                'className' => 'show-todo',
                'template'  => 'todo/components/link.html.twig',
            ])
            ->add('name', TextColumn::class, ['label' => 'Créateur', 'field' => 'Owner.name'])
            ->add('createdAt', DateTimeColumn::class, ['label' => 'Date de création', 'format' => 'd/m/Y'])
            ->add('id', TwigColumn::class, [
                'label'     => 'Actions',
                'className' => 'actions',
                'template'  => 'todo/components/actions.html.twig',
            ])
            ->createAdapter(ORMAdapter::class, [
                'entity'  => Todo::class,
                'hydrate' => Query::HYDRATE_ARRAY,
                'query'   => function (QueryBuilder $builder)  use ($user) {
                    $builder
                        ->distinct()
                        ->select('Todo.id, Todo.title, Todo.createdAt, UserTodo.isOwner, Owner.name')
                        ->from(User::class, 'User')
                        ->innerJoin(UserTodo::class, 'UserTodo', Join::WITH, 'UserTodo.user = User.id')
                        ->innerJoin(Todo::class, 'Todo', Join::WITH, 'UserTodo.todo = Todo.id')
                        ->innerJoin(UserTodo::class, 'OwnerTodo', Join::WITH, 'OwnerTodo.todo = Todo.id And OwnerTodo.isOwner = :isOwner')
                        ->innerJoin(User::class, 'Owner', Join::WITH, 'OwnerTodo.user = Owner.id')
                        ->setParameter('isOwner', true)
                        ->andWhere('User = :user')
                        ->setParameter('user', $user)
                        ->orderBy('Todo.id');
                },
            ])
            ->handleRequest($request);

        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render('todo/index.html.twig', ['table' => $table, 'form' => $form->createView()]);
    }

    /**
     * @Route("/todo/{id<\d+>}", name="todo_show")
     */
    public function show(Todo $todo, Request $request, DataTableFactory $dataTableFactory, EntityManagerInterface $entityManager): Response
    {
        $task = new Task();
        $user = $this->getUser();
        $form = $this->createForm(TaskFormType::class, $task)->handleRequest($request);

        if ( ! $todo) {
            $this->addFlash('danger', 'Aucune liste de tâches trouvée.');
            return $this->redirectToRoute('todo');
        } else if ( ! $entityManager->getRepository(Todo::class)->checkHasPermission($todo, $user)) {
            $this->addFlash('danger', "Vous n'êtes pas autorisé à afficher cette liste de tâches.");
            return $this->redirectToRoute('todo');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            if ($entityManager->getRepository(Todo::class)->checkIsOwner($todo, $user)) {
                $task->setBody($form->get('body')->getData());
                $todo->addTask($task);

                $entityManager->persist($task);
                $entityManager->flush();

                $this->addFlash('success', 'Votre tâche a été ajoutée avec succès.');
            } else {
                $this->addFlash('danger', "Vous n'avez pas la permission d'ajouter une tâche.");
            }
        }

        $table = $dataTableFactory->create()
            ->add('completed', TwigColumn::class, [
                'label'     => 'Statut',
                'template'  => 'task/components/statut.html.twig',
            ])
            ->add('body', TwigColumn::class, [
                'label'    => 'Tâche',
                'template' => 'task/components/body.html.twig',
            ])
            ->add('id', TwigColumn::class, [
                'label'     => 'Actions',
                'className' => 'actions',
                'template'  => 'task/components/actions.html.twig',
            ])
            ->createAdapter(ORMAdapter::class, [
                'entity'  => Task::class,
                'hydrate' => Query::HYDRATE_ARRAY,
                'query'   => function (QueryBuilder $builder) use ($todo, $user) {
                    $builder
                        ->select('Task.id, Task.body, Task.completed, UserTodo.isOwner')
                        ->from(Task::class, 'Task')
                        ->innerJoin(Todo::class, 'Todo', Join::WITH, 'Task.todo = Todo.id')
                        ->innerJoin(UserTodo::class, 'UserTodo', Join::WITH, 'UserTodo.todo = Todo.id')
                        ->andWhere('Todo = :todo')
                        ->setParameter('todo', $todo)
                        ->andWhere('UserTodo.user = :user')
                        ->setParameter('user', $user);
                },
            ])
            ->handleRequest($request);

        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render('todo/show.html.twig', ['table' => $table, 'form' => $form->createView()]);
    }

    /**
     * @Route("/todo/{todo<\d+>}/clone", name="todo_clone", methods={"POST"})
     */
    public function clone(Todo $todo, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if ( ! $todo) {
            $this->addFlash('danger', 'Aucune liste de tâches trouvée.');
        } else {
            if ($entityManager->getRepository(Todo::class)->checkHasPermission($todo, $user)) {
                $todo = clone $todo;
                $now  = new DateTime();

                $todo->setCreatedAt($now)
                    ->setUpdatedAt($now);

                $entityManager->persist($todo);

                $userTodo = new UserTodo();

                $userTodo->setUser($user)
                    ->setTodo($todo)
                    ->setIsOwner(true);

                $entityManager->persist($userTodo);
                $entityManager->flush();

                $this->addFlash('success', 'Votre liste de tâches a été créée avec succès.');

                return $this->redirectToRoute('todo_show', ['id' => $todo->getId()]);
            } else {
                $this->addFlash('danger', "Vous n'avez pas la permission de coiper cette liste de tâches.");
            }
        }

        return $this->redirectToRoute('todo');
    }

    /**
     * @Route("/todo/{todo<\d+>}/share/{user<\d+>}", name="todo_share", methods={"POST"})
     */
    public function share(Todo $todo, User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        $owner = $this->getUser();

        if ( ! $todo) {
            $this->addFlash('danger', 'Aucune liste de tâches trouvée.');
        } else {
            if ($entityManager->getRepository(Todo::class)->checkIsOwner($todo, $owner)) {
                $userTodo = new UserTodo();

                $userTodo->setUser($user)
                    ->setTodo($todo);

                $entityManager->persist($userTodo);
                $entityManager->flush();

                $this->addFlash('success', 'Votre liste de tâches a été partagée avec succès.');
            } else {
                $this->addFlash('danger', "Vous n'êtes pas autorisé à partager cette liste de tâches.");
            }
        }

        return $this->redirectToRoute('todo');
    }

    /**
     * @Route("/todo/{todo<\d+>}/delete", name="todo_delete", methods={"GET", "DELETE"})
     */
    public function delete(Todo $todo, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if ( ! $todo) {
            $this->addFlash('danger', 'Aucune liste de tâches trouvée.');
        } else {
            if ($entityManager->getRepository(Todo::class)->checkIsOwner($todo, $user)) {
                $entityManager->remove($todo);
                $entityManager->flush();
                $this->addFlash('success', 'votre liste de tâches a été supprimée avec succès.');
            } else {
                $this->addFlash('danger', "Vous n'avez pas la permission de supprimer cette liste de tâches.");
            }
        }

        return $this->redirectToRoute('todo');
    }
}
