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
                'template'  => 'components/link.html.twig',
            ])
            ->add('name', TextColumn::class, ['label' => 'Créateur', 'field' => 'User.name'])
            ->add('createdAt', DateTimeColumn::class, ['label' => 'Date de création', 'format' => 'd/m/Y'])
            ->add('id', TwigColumn::class, [
                'label'     => 'Actions',
                'className' => 'actions',
                'template'  => 'components/actions.html.twig',
            ])
            ->createAdapter(ORMAdapter::class, [
                'entity'  => Todo::class,
                'hydrate' => Query::HYDRATE_ARRAY,
                'query'   => function (QueryBuilder $builder) {
                    $builder
                        ->select('Todo.id, Todo.title, Todo.createdAt, UserTodo.isOwner, User.name')
                        ->from(Todo::class, 'Todo')
                        ->innerJoin(UserTodo::class, 'UserTodo', Join::WITH, 'UserTodo.todo = Todo.id')
                        ->innerJoin(User::class, 'User', Join::WITH, 'UserTodo.user = User.id');
                },
                'criteria' => [
                    function (QueryBuilder $builder) use ($user) {
                        $builder
                            ->andWhere('User = :user')
                            ->setParameter('user', $user);
                    },
                    new SearchCriteriaProvider(),
                ],
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
    public function show(int $id, Request $request, DataTableFactory $dataTableFactory, EntityManagerInterface $entityManager): Response
    {
        $task = new Task();
        $user = $this->getUser();
        $todo = $entityManager->getRepository(Todo::class)->find($id);
        $form = $this->createForm(TaskFormType::class, $task)->handleRequest($request);

        if ( ! $todo) {
            $this->addFlash('danger', 'Aucune liste de tâches trouvée.');
            return $this->redirectToRoute('todo');
        } else if ( ! $entityManager->getRepository(Todo::class)->checkHasPermission($todo, $user)) {
            $this->addFlash('danger', "Vous n'avez pas la permission de coiper ceci.");
            return $this->redirectToRoute('todo');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setBody($form->get('body')->getData());
            $todo->addTask($task);

            $entityManager->persist($task);
            $entityManager->flush();

            $this->addFlash('success', 'Votre liste de tâches a été créée avec succès.');
        }

        $table = $dataTableFactory->create()
            ->add('completed', BoolColumn::class, [
                'trueValue'  => 'yes',
                'falseValue' => 'no',
            ])
            ->add('body', TextColumn::class)
            ->add('id', TextColumn::class)
            ->createAdapter(ORMAdapter::class, [
                'entity'  => Task::class,
                'hydrate' => Query::HYDRATE_ARRAY,
                'query'   => function (QueryBuilder $builder) {
                    $builder
                        ->select('Task')
                        ->from(Task::class, 'Task')
                        ->innerJoin(Todo::class, 'Todo', Join::WITH, 'Task.todo = Todo.id');
                },
                'criteria' => [
                    function (QueryBuilder $builder) use ($todo) {
                        $builder
                            ->andWhere('Todo = :todo')
                            ->setParameter('todo', $todo);
                    },
                    new SearchCriteriaProvider(),
                ],
            ])
            ->handleRequest($request);

        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render('todo/show.html.twig', ['table' => $table, 'form' => $form->createView()]);
    }

    /**
     * @Route("/todo/{id<\d+>}/clone", name="todo_clone", methods={"GET", "POST"})
     */
    public function clone(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $todo = $entityManager->getRepository(Todo::class)->find($id);

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
                $this->addFlash('danger', "Vous n'avez pas la permission de coiper ceci.");
            }
        }

        return $this->redirectToRoute('todo');
    }

    /**
     * @Route("/todo/create", name="todo_create", methods={"POST"})
     */
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $todo = new Todo();
        $now  = new DateTime();

        $todo->setTitle($request->get('title'))
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

        return $this->redirect('todo_show', ['id' => $todo->getId()]);
    }

    /**
     * @Route("/todo/{id<\d+>}/delete", name="todo_delete", methods={"GET", "DELETE"})
     */
    public function delete(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $todo = $entityManager->getRepository(Todo::class)->find($id);

        if ( ! $todo) {
            $this->addFlash('danger', 'Aucune liste de tâches trouvée.');
        } else {
            if ($entityManager->getRepository(Todo::class)->checkIsOwner($todo, $user)) {
                $entityManager->remove($todo);
                $entityManager->flush();
                $this->addFlash('success', 'votre liste de tâches a été supprimée avec succès.');
            } else {
                $this->addFlash('danger', "Vous n'avez pas la permission de supprimer ceci.");
            }
        }

        return $this->redirectToRoute('todo');
    }
}
