<?php

declare(strict_types=1);

namespace App\User\SignUp\Http;

use App\Infrastructure\ApiException\ApiBadResponseException;
use App\Infrastructure\ApiException\ApiErrorCode;
use App\Infrastructure\ApiException\ApiNotFoundException;
use App\Infrastructure\SuccessResponse;
use App\User\SignUp\Command\ConfirmEmail;
use App\User\SignUp\Domain\EmailAlreadyIsConfirmedException;
use App\User\SignUp\Domain\UserNotFoundException;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/confirm-email/{confirmToken}', methods: ['GET'])]
#[AsController]
final class ConfirmEmailAction
{
    public function __construct(private readonly ConfirmEmail $confirmEmail)
    {
    }

    public function __invoke(Uuid $confirmToken): SuccessResponse
    {
        try {
            ($this->confirmEmail)($confirmToken);
        } catch (EmailAlreadyIsConfirmedException $e) {
            throw new ApiBadResponseException($e->getMessage(), ApiErrorCode::EmailAlreadyIsConfirmed);
        } catch (UserNotFoundException $e) {
            throw new ApiNotFoundException($e->getMessage());
        }

        return new SuccessResponse();
    }
}
