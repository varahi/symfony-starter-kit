<?php

declare(strict_types=1);

namespace App\Task\Command\UpdateTaskName;

use App\Infrastructure\ArgumentValueResolver\ApiRequest;
use Webmozart\Assert\Assert;

final class UpdateTaskNameCommand implements ApiRequest
{
    public function __construct(public readonly string $taskName)
    {
        Assert::notEmpty($taskName);
    }
}