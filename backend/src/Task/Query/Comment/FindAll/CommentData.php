<?php

declare(strict_types=1);

namespace App\Task\Query\Comment\FindAll;

use Symfony\Component\Uid\Uuid;

final class CommentData
{
    public function __construct(
        public readonly Uuid $id,
        public readonly string $body,
        public readonly \DateTimeImmutable $createdAt,
        public readonly ?\DateTimeImmutable $updatedAt,
    ) {
    }
}
