<?php

declare(strict_types=1);

namespace App\User\SignUp\Http;

use App\Infrastructure\ApiException\ApiUnauthorizedException;
use App\Infrastructure\AsService;
use App\User\SignUp\Domain\User;
use App\User\SignUp\Domain\UserId;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

#[AsService]
final class UserIdArgumentValueResolver implements ValueResolverInterface
{
    public function __construct(private readonly Security $security)
    {
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     *
     * @return iterable<UserId>
     *
     * @throws ApiUnauthorizedException
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== UserId::class) {
            return [];
        }

        $user = $this->security->getUser();

        if ($user === null) {
            throw new ApiUnauthorizedException();
        }

        if ($user instanceof User === false) {
            throw new ApiUnauthorizedException();
        }

        return [$user->getUserId()];
    }
}
