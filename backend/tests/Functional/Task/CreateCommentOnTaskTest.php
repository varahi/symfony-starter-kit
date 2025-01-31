<?php

declare(strict_types=1);

namespace App\Tests\Functional\Task;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Task;
use App\Tests\Functional\SDK\User;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 */
final class CreateCommentOnTaskTest extends ApiWebTestCase
{
    public function testSuccessfulCreationTask(): void
    {
        $token = User::auth();

        $taskId = Task::createAndReturnId('First task', $token);

        $body = [];
        $body['commentBody'] = $commentText = 'First comment';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', "/api/tasks/{$taskId}/add-comment", $body, token: $token);
        self::assertSuccessResponse($response);

        $response = self::request('GET', "/api/tasks/{$taskId}/comments", token: $token);
        $comments = self::jsonDecode($response->getContent());

        self::assertCount(1, $comments);
        self::assertSame($commentText, $comments[0]['body']);
        self::assertNotNull($comments[0]['id']);
        self::assertNotNull($comments[0]['createdAt']);
        self::assertNull($comments[0]['updatedAt']);
    }

    public function testNotFound(): void
    {
        $token = User::auth();

        Task::create('Тестовая задача 1', $token);

        $body = [];
        $body['commentBody'] = 'First comment';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $taskId = (string) Uuid::v4();
        $response = self::request('POST', "/api/tasks/{$taskId}/add-comment", $body, token: $token);
        self::assertNotFound($response);
    }

    public function testAddCommentToCompletedTask(): void
    {
        $token = User::auth();
        $taskId = Task::createAndReturnId('First task', $token);

        self::request('POST', "/api/tasks/{$taskId}/complete", token: $token);

        $body = [];
        $body['commentBody'] = 'First comment';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', "/api/tasks/{$taskId}/add-comment", $body, token: $token);
        self::assertBadRequest($response);
    }

    /**
     * @dataProvider notValidTokenDataProvider
     */
    public function testAccessDenied(string $notValidToken): void
    {
        $token = User::auth();
        $taskId = Task::createAndReturnId('Тестовая задача 1', $token);

        $body = [];
        $body['commentBody'] = $commentText = 'First comment';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', "/api/tasks/{$taskId}/add-comment", $body, token: $notValidToken);

        self::assertAccessDenied($response);
    }

    public function testNoAccessAnotherUser(): void
    {
        $token = User::auth();
        Task::create('Тестовая задача №1', $token);

        $this->tearDown();
        $tokenSecond = User::auth('second@example.com');
        $taskId = Task::createAndReturnId('Тестовая задача №2 ', $tokenSecond);

        $body = [];
        $body['commentBody'] = $commentText = 'First comment';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request('POST', "/api/tasks/{$taskId}/add-comment", $body, token: $token);

        self::assertNotFound($response);
    }
}
