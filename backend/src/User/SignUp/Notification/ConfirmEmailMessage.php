<?php

declare(strict_types=1);

namespace App\User\SignUp\Notification;

use App\Infrastructure\Message;
use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;

final class ConfirmEmailMessage implements Message
{
    public function __construct(private readonly Uuid $confirmToken, private readonly string $email)
    {
        Assert::notEmpty($email);
        Assert::email($email);
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getConfirmToken(): Uuid
    {
        return $this->confirmToken;
    }
}
