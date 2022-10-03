<?php

declare(strict_types=1);

namespace App\Task\Http;

use App\Infrastructure\ApiException\ApiNotFoundException;
use App\Infrastructure\ApiException\ApiUnauthorizedException;
use App\Task\Query\Task\FindById\FindTaskById;
use App\Task\Query\Task\FindById\FindTaskByIdQuery;
use App\Task\Query\Task\FindById\TaskData;
use App\Task\Query\Task\FindById\TaskNotFoundException;
use App\User\Domain\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Uid\Uuid;

#[IsGranted('ROLE_USER')]
#[Route('/tasks/{id}', methods: ['GET'])]
#[AsController]
final class TaskInfoAction
{
    public function __construct(private readonly FindTaskById $findTaskById)
    {
    }

    public function __invoke(Uuid $id, #[CurrentUser] ?User $user): TaskData
    {
        if ($user === null) {
            throw new ApiUnauthorizedException();
        }

        try {
            $taskData = ($this->findTaskById)(new FindTaskByIdQuery($id, $user->getId()));
        } catch (TaskNotFoundException $e) {
            throw new ApiNotFoundException($e->getMessage());
        }

        return $taskData;
    }
}