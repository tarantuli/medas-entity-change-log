<?php

declare(strict_types=1);

namespace Medas\EntityChangeLogTest\Functional;

use Medas\EntityChangeLog\AfterChangeHandler;
use Medas\EntityChangeLogTest\MockUps\Task;
use Medas\EntityManager\Snapshots\{Changes, PropertyChange};
use PHPUnit\Framework\TestCase;

class AfterChangeHandlerTest extends TestCase
{
    public function testCreation(): void
    {
        $task = new Task('Test change log');

        em()->persist($task);
        em()->flush();

        $changes = new Changes();

        $changes->addCreate($task, []);

        $job = service(AfterChangeHandler::class)->processChanges($changes);

        self::assertCount(1, $job->entries);
    }

    public function testUpdate(): void
    {
        $task = new Task('Test change log');

        em()->persist($task);
        em()->flush();

        $task->description = 'Test change log 2';

        em()->flush();

        $changes = new Changes();

        $changes->addUpdate(
            $task,
            ['description' => new PropertyChange('Test change log', 'Test change log 2')]
        );

        $job = service(AfterChangeHandler::class)->processChanges($changes);

        self::assertCount(1, $job->entries);
    }

    public function testUpdateToLongString(): void
    {
        $initialDescription
            = "gzcompress produces longer data because it embeds information about the encoding onto the string. \nIf you are compressing data that will only ever be handled on one machine, then you don't need to worry about which of these functions you use. \nHowever, if you are passing data compressed with these functions to a different machine you should use gzcompress.";

        $secondDescreiption = $initialDescription . "\n Hi";
        $task = new Task($initialDescription);

        em()->persist($task);
        em()->flush();

        $task->description = $secondDescreiption;

        em()->flush();

        $changes = new Changes();

        $changes->addUpdate(
            $task,
            ['description' => new PropertyChange($initialDescription, $secondDescreiption)]
        );

        $job = service(AfterChangeHandler::class)->processChanges($changes);

        self::assertCount(1, $job->entries);
    }
}
