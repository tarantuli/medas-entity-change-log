<?php

declare(strict_types=1);

namespace Medas\EntitiesChangelogTest\Functional;

use Medas\EntitiesChangelog\AfterChangeHandler;
use Medas\EntitiesChangelogTest\MockUps\Task;
use Medas\EntityManager\Entities\Changes;
use PHPUnit\Framework\TestCase;

class AfterChangeHandlerTest extends TestCase
{
    public function testCreation(): void
    {
        $task = new Task('Test changelog');

        em()->persist($task);
        em()->flush();

        $changes = new Changes();

        $changes->addCreate($task);

        $job = service(AfterChangeHandler::class)->processChanges($changes);

        self::assertCount(1, $job->entries);
    }
}
