<?php

declare(strict_types=1);

namespace App\User\SignIn\Http;

use Symfony\Component\Uid\Uuid;

final class UserResponse
{
    public function __construct(
        public readonly Uuid $token,
    ) {
    }
}
