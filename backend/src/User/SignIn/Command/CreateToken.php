<?php

declare(strict_types=1);

namespace App\User\SignIn\Command;

use App\Infrastructure\AsService;
use App\Infrastructure\Flush;
use App\User\SignIn\Domain\UserToken;
use App\User\SignIn\Domain\UserTokens;
use App\User\SignUp\Domain\UserId;
use Symfony\Component\Uid\Uuid;

#[AsService]
final class CreateToken
{
    public function __construct(private readonly UserTokens $userTokens, private readonly Flush $flush)
    {
    }

    public function __invoke(UserId $userId, Uuid $userTokenId): void
    {
        $userToken = new UserToken($userTokenId, $userId);
        $this->userTokens->add($userToken);

        ($this->flush)();
    }
}
