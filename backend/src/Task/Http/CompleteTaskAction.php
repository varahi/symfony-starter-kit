<?php

declare(strict_types=1);

namespace App\Task\Http;

use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Infrastructure\SuccessResponse;
use App\Task\Command\CompleteTask;
use App\Task\Domain\Task;
use App\Task\Domain\TaskAlreadyIsDoneException;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/tasks/{taskId}/complete', methods: ['POST'])]
#[AsController]
final class CompleteTaskAction
{
    public function __construct(private readonly CompleteTask $completeTask)
    {
    }

    public function __invoke(Task $task): SuccessResponse
    {
        try {
            ($this->completeTask)($task);
        } catch (TaskAlreadyIsDoneException $e) {
            throw new ApiBadRequestException($e->getMessage());
        }

        return new SuccessResponse();
    }
}
