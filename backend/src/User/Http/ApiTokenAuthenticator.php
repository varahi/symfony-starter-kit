<?php

declare(strict_types=1);

namespace App\User\Http;

use App\Infrastructure\ApiException\ApiErrorResponse;
use App\Infrastructure\ApiException\ApiUnauthorizedException;
use App\User\Model\UserTokens;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;

final class ApiTokenAuthenticator extends AbstractAuthenticator
{
    public const TOKEN_NAME = 'X-AUTH-TOKEN';

    public function __construct(
        private readonly UserTokens $userTokens,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has(self::TOKEN_NAME);
    }

    public function authenticate(Request $request): Passport
    {
        $apiToken = $request->headers->get(self::TOKEN_NAME);
        if ($apiToken === null) {
            throw new CustomUserMessageAuthenticationException('Не передан токен');
        }

        $userToken = $this->userTokens->findById(Uuid::fromString($apiToken));
        if ($userToken === null) {
            throw new CustomUserMessageAuthenticationException('Токен не найден');
        }

        return new SelfValidatingPassport(new UserBadge($userToken->getUser()->getUserEmail()->getValue()));
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());
        $apiException = new ApiUnauthorizedException($message);
        $content = $this->serializer->serialize(
            new ApiErrorResponse($apiException->getErrorMessage(), $apiException->getApiCode()),
            JsonEncoder::FORMAT
        );

        return new JsonResponse($content, $apiException->getHttpCode(), [], true);
    }
}
