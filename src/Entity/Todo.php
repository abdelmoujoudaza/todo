<?php

namespace App\Entity;

use App\Repository\TodoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=TodoRepository::class)
 */
class Todo
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\OneToMany(targetEntity=Task::class, mappedBy="todo", cascade={"persist"})
     */
    private $tasks;

    /**
     * @ORM\OneToMany(targetEntity=UserTodo::class, mappedBy="todo", cascade={"persist"})
     */
    private $todoUsers;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
        $this->todoUsers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection|Task[]
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): self
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks[] = $task;
            $task->setTodo($this);
        }

        return $this;
    }

    public function removeTask(Task $task): self
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getTodo() === $this) {
                $task->setTodo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserTodo[]
     */
    public function getTodoUsers(): Collection
    {
        return $this->todoUsers;
    }

    public function addTodoUser(UserTodo $todoUser): self
    {
        if (!$this->todoUsers->contains($todoUser)) {
            $this->todoUsers[] = $todoUser;
            $todoUser->setTodo($this);
        }

        return $this;
    }

    public function removeTodoUser(UserTodo $todoUser): self
    {
        if ($this->todoUsers->removeElement($todoUser)) {
            // set the owning side to null (unless already changed)
            if ($todoUser->getTodo() === $this) {
                $todoUser->setTodo(null);
            }
        }

        return $this;
    }

    public function __clone() {
        if ($this->id) {
            $this->id        = null;
            $this->todoUsers = new ArrayCollection();
            $tasks           = $this->getTasks();
            $this->tasks     = new ArrayCollection();

            foreach ($tasks as $task) {
                $taskClone = clone $task;
                $taskClone->setTodo($this);
                $this->addTask($taskClone);
            }
        }
    }
}
