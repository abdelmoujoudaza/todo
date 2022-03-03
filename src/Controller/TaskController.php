<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\Todo;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TaskController extends AbstractController
{
    /**
     * @Route("/task/{task<\d+>}/update", name="task_update", methods={"POST", "PUT"})
     */
    public function update(Task $task, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if ( ! $task) {
            $this->addFlash('danger', 'Aucune tâche trouvée.');
            return $this->redirectToRoute('todo');
        } else {
            if ($entityManager->getRepository(Todo::class)->checkIsOwner($task->getTodo(), $user)) {
                $task->setCompleted( ! $task->getCompleted());
                $entityManager->persist($task);
                $entityManager->flush();
                $this->addFlash('success', 'Votre tâche a été mise à jour avec succès.');
            } else {
                $this->addFlash('danger', "vous n'avez pas la permission de mettre à jour cette tâche.");
            }
        }

        return $this->redirectToRoute('todo_show', ['todo' => $task->getTodo()->getId()]);
    }

    /**
     * @Route("/task/{task<\d+>}/delete", name="task_delete", methods={"GET", "DELETE"})
     */
    public function delete(Task $task, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if ( ! $task) {
            $this->addFlash('danger', 'Aucune tâche trouvée.');
            return $this->redirectToRoute('todo');
        } else {
            if ($entityManager->getRepository(Todo::class)->checkIsOwner($task->getTodo(), $user)) {
                $entityManager->remove($task);
                $entityManager->flush();
                $this->addFlash('success', 'Votre tâche a été supprimée avec succès.');
            } else {
                $this->addFlash('danger', "Vous n'avez pas la permission de supprimer cette tâche.");
            }
        }

        return $this->redirectToRoute('todo_show', ['todo' => $task->getTodo()->getId()]);
    }
}
