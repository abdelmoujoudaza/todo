<?php

namespace App\Controller;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TaskController extends AbstractController
{
    /**
     * @Route("/task/{id<\d+>}/update", name="todo_update", methods={"POST", "PUT"})
     */
    public function update(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $task = $entityManager->getRepository(Task::class)->find($id);

        if ( ! $task) {
            $this->addFlash('danger', 'Aucune tâche trouvée.');
            return $this->redirectToRoute('todo');
        } else {
            if ($entityManager->getRepository(Todo::class)->checkIsOwner($task->getTodo(), $user)) {
                $task->setCompleted(true);
                $entityManager->persist($task);
                $entityManager->flush();
                $this->addFlash('success', 'Votre tâche a été supprimée avec succès.');
            } else {
                $this->addFlash('danger', "vous n'avez pas la permission de mettre à jour ceci.");
            }
        }

        return $this->redirect('todo_show', ['id' => $task->getTodo()->getId()]);
    }

    /**
     * @Route("/task/{id<\d+>}/delete", name="todo_delete", methods={"GET", "DELETE"})
     */
    public function delete(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $task = $entityManager->getRepository(Task::class)->find($id);

        if ( ! $task) {
            $this->addFlash('danger', 'Aucune tâche trouvée.');
            return $this->redirectToRoute('todo');
        } else {
            if ($entityManager->getRepository(Todo::class)->checkIsOwner($task->getTodo(), $user)) {
                $entityManager->remove($task);
                $entityManager->flush();
                $this->addFlash('success', 'Votre tâche a été supprimée avec succès.');
            } else {
                $this->addFlash('danger', "Vous n'avez pas la permission de supprimer ceci.");
            }
        }

        return $this->redirect('todo_show', ['id' => $task->getTodo()->getId()]);
    }
}
