<?php

declare(strict_types=1);

namespace App\Task\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
/** @final */
class Task
{
    #[ORM\Id, ORM\Column(type: 'uuid', unique: true)]
    private readonly Uuid $id;

    #[ORM\Column(type: 'uuid')]
    private Uuid $userId;

    #[ORM\Embedded]
    private TaskName $taskName;

    #[ORM\Column]
    private bool $isCompleted;

    /**
     * @var Collection<int, TaskComment>
     */
    #[ORM\OneToMany(mappedBy: 'task', targetEntity: TaskComment::class, cascade: ['all'], orphanRemoval: true)]
    private Collection $comments;

    #[ORM\Column]
    private readonly \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $completedAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt;

    public function __construct(TaskId $taskId, TaskName $taskName, Uuid $userId)
    {
        $this->id = $taskId->getValue();
        $this->taskName = $taskName;
        $this->userId = $userId;
        $this->createdAt = new \DateTimeImmutable();
        $this->completedAt = null;
        $this->updatedAt = null;
        $this->isCompleted = false;
        $this->comments = new ArrayCollection();
    }

    public function changeTaskName(TaskName $taskName): void
    {
        $this->taskName = $taskName;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function markAsDone(): void
    {
        if ($this->isCompleted === true) {
            throw new TaskAlreadyIsDoneException('Задача уже выполнена');
        }

        $this->isCompleted = true;
        $this->updatedAt = new \DateTimeImmutable();
        $this->completedAt = new \DateTimeImmutable();
    }

    public function addComment(TaskComment $taskComment): void
    {
        if ($this->isCompleted === true) {
            throw new AddCommentToCompletedTaskException('Нельзя добавлять комментарии в выполненную задачу');
        }

        $this->comments->add($taskComment);
    }
}