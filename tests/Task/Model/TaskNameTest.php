<?php

namespace App\Tests\Task\Model;

use App\Task\Model\TaskName;
use PHPUnit\Framework\TestCase;

class TaskNameTest extends TestCase
{
    public function testCorrectName(): void
    {
        $taskName = new TaskName($expectedName = 'new task');

        self::assertSame($expectedName, $taskName->getValue());
    }

    public function testEmptyName(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new TaskName('');
    }
}
