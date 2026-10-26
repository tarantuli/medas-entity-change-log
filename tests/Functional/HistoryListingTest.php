<?php

declare(strict_types=1);

namespace Medas\EntityChangeLogTest\Functional;

use Medas\EntityChangeLog\{AfterChangeHandler, Change\EntityController, Change\HistoryListing, Job};
use Medas\EntityChangeLogTest\MockUps\{Note, Remark, Task};
use Medas\EntityManager\Snapshots\{Changes, PropertyChange, Snapshot};
use PHPUnit\Framework\TestCase;

class HistoryListingTest extends TestCase
{
    public function testEntityWithoutOwnersIsListedUnderItselfOnly(): void
    {
        $task = $this->persisted(new Task('Without owners'));
        $job = $this->processCreation($task);

        self::assertCount(1, $job->entries);
        self::assertSame([$task], $this->listedEntities($job));
    }

    public function testChangeIsListedUnderOwner(): void
    {
        $task = $this->persisted(new Task('Owner'));
        $note = $this->persisted(new Note($task, 'Owned'));
        $job = $this->processCreation($note);

        self::assertSame([$note, $task], $this->listedEntities($job));
        self::assertSame($job->entries[0], $job->listings[1]->entry);
    }

    public function testOwnersAreFollowedTransitively(): void
    {
        $task = $this->persisted(new Task('Top owner'));
        $note = $this->persisted(new Note($task, 'Middle'));
        $remark = $this->persisted(new Remark($note, 'Bottom'));
        $job = $this->processCreation($remark);

        self::assertSame([$remark, $note, $task], $this->listedEntities($job));
    }

    public function testEmptyOwnerRelationIsSkipped(): void
    {
        $note = $this->persisted(new Note(null, 'No owner'));
        $job = $this->processCreation($note);

        self::assertSame([$note], $this->listedEntities($job));
    }

    private function processCreation(object $entity): Job
    {
        $changes = new Changes();

        $changes->addCreate($entity, [], new Snapshot());

        return service(AfterChangeHandler::class)->processChanges($changes);
    }

    public function testEveryPropertyChangeIsListedUnderOwner(): void
    {
        $task = $this->persisted(new Task('Owner of changes'));
        $note = $this->persisted(new Note($task, 'Before'));
        $changes = new Changes();

        $changes->addUpdate(
            $note,
            ['text' => new PropertyChange('Before', 'After')],
            new Snapshot()
        );

        $job = service(AfterChangeHandler::class)->processChanges($changes);

        self::assertCount(1, $job->entries);
        self::assertSame([$note, $task], $this->listedEntities($job));
    }

    public function testDeletionIsListedUnderOwner(): void
    {
        $task = $this->persisted(new Task('Owner of deleted'));
        $note = $this->persisted(new Note($task, 'Deleted'));
        $changes = new Changes();

        $changes->addDelete($note);

        $job = service(AfterChangeHandler::class)->processChanges($changes);

        self::assertSame([$note, $task], $this->listedEntities($job));
    }

    private function persisted(object $entity): object
    {
        em()->persist($entity);
        em()->flush();

        return $entity;
    }

    /**
     * Maps each listing back to the test entity it names, in listing order
     *
     * @return object[]
     */
    private function listedEntities(Job $job): array
    {
        $entityController = service(EntityController::class);
        $candidates = [];

        foreach ($job->listedUnder as $changedEntity) {
            foreach ($job->listedUnder[$changedEntity] as $listedEntity) {
                $candidates[] = $listedEntity;
            }
        }

        return array_map(function (HistoryListing $listing) use ($entityController, $candidates) {
            foreach ($candidates as $candidate) {
                if ($entityController->getChangeEntity($candidate) === $listing->entity
                        && $entityController->getEntityId($candidate) === $listing->entityId) {
                    return $candidate;
                }
            }

            self::fail('A listing names an entity that is not part of the change');
        }, $job->listings);
    }
}
