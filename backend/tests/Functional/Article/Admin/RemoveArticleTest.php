<?php

declare(strict_types=1);

namespace App\Tests\Functional\Article\Admin;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Article;
use App\Tests\Functional\SDK\User;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 */
final class RemoveArticleTest extends ApiWebTestCase
{
    public function testSuccess(): void
    {
        $token = User::auth();

        $articleId1 = Article::createAndReturnId('Статья1', 'statya', '<p>Контент</p>', $token);
        $articleId2 = Article::createAndReturnId('Статья2', 'statya2', '<p>Контент</p>', $token);

        $response = self::request('POST', "/api/admin/article/{$articleId1}/remove", token: $token);
        self::assertSuccessContentResponse($response);

        $articles = Article::list($token);

        self::assertCount(1, $articles);
        self::assertSame($articleId2, $articles[0]['id']);
    }

    public function testNotFound(): void
    {
        $token = User::auth();

        Article::create('Статья', 'statya', '<p>Контент</p>', $token);

        $articleId = (string) Uuid::v4();
        $response = self::request('POST', "/api/admin/article/{$articleId}/remove", token: $token);
        self::assertNotFound($response);
    }

    /**
     * @dataProvider notValidTokenDataProvider
     */
    public function testAccessDenied(string $notValidToken): void
    {
        $token = User::auth();
        $articleId = Article::createAndReturnId('Статья1', 'statya', '<p>Контент</p>', $token);

        $response = self::request('POST', "/api/admin/article/{$articleId}/remove", token: $notValidToken);

        self::assertAccessDenied($response);
    }
}
