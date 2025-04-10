<?php

declare(strict_types=1);

namespace Medas\EntityChangeLogTest\Functional;

use Medas\EntityChangeLog\AfterChangeHandler;
use Medas\EntityChangeLogTest\MockUps\Task;
use Medas\EntityManager\Entities\Changes;
use PHPUnit\Framework\TestCase;

class AfterChangeHandlerTest extends TestCase
{
    public function testCreation(): void
    {
        $task = new Task('Test change log');

        em()->persist($task);
        em()->flush();

        $changes = new Changes();

        $changes->addCreate($task);

        $job = service(AfterChangeHandler::class)->processChanges($changes);

        self::assertCount(1, $job->entries);
    }

    public function testUpdate(): void
    {
        $task = new Task('Test change log');

        em()->persist($task);
        em()->flush();

        $task->description = 'new description';

        em()->flush();

        $changes = new Changes();

        $changes->addUpdate($task, ['description' => 'new description']);

        $job = service(AfterChangeHandler::class)->processChanges($changes);

        self::assertCount(1, $job->entries);
    }
}
