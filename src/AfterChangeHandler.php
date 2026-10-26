<?php

declare(strict_types=1);

namespace Medas\EntityChangeLog;

use Medas\Core\{Attributes\Service, Interfaces\EntityManager};
use Medas\EntityManager\Attributes\Changes\{DontLogChanges, LogChanges};
use Medas\EntityManager\Entities\AfterFlushHandler;
use Medas\EntityManager\Snapshots\Changes;

#[Service]
readonly class AfterChangeHandler implements AfterFlushHandler
{
    public function __construct(
        private Change\EntityController $entityController,
        private Change\EntryController  $entryController,
        private Change\OwnerResolver    $ownerResolver,
        private EntityManager           $entityManager,
    )
    {
    }

    public function handle(Changes $changes): bool
    {
        $job = $this->processChanges($changes);

        if ($job->dispatchedEvents) {
            $this->entityManager->persist(...$job->entries, ...$job->listings);
        }

        return $job->dispatchedEvents;
    }

    public function processChanges(Changes $changes): Job
    {
        $job = new Job($changes);

        $this->handleEntities(
            $job,
            $changes->createdEntities(),
            fn($entity) => $this->logCreation($job, $entity)
        );

        $this->handleEntities(
            $job,
            $changes->updatedEntities(),
            fn($entity) => $this->logChanges($job, $entity)
        );

        $this->handleEntities(
            $job,
            $changes->deletedEntities(),
            fn($entity) => $this->logDeletion($job, $entity)
        );

        return $job;
    }

    private function logCreation(Job $job, object $entity): void
    {
        $entry = $this->createEntry($job, $entity);

        $entry->type = Change\EntryType::EntityCreation;
        $job->entries[] = $entry;

        foreach ($job->changes->createValues($entity) as $property => $propertyChange) {
            $this->processProperty($job, $entity, $property, null, $propertyChange->current);
        }
    }

    private function logChanges(Job $job, object $entity): void
    {
        foreach ($job->changes->entityChanges($entity) as $property => $propertyChange) {
            $this->processProperty(
                $job,
                $entity,
                $property,
                $propertyChange->previous,
                $propertyChange->current
            );
        }
    }

    private function processProperty(
        Job        $job,
        object     $entity,
        int|string $property,
        mixed      $previous,
        mixed      $current
    ): void
    {
        if (attribute(DontLogChanges::class, new \ReflectionProperty($entity, $property))) {
            return;
        }

        $entry = $this->createEntry($job, $entity);

        if ($previous === null) {
            $entry->type = Change\EntryType::PropertySet;
        }
        elseif ($current === null) {
            $entry->type = Change\EntryType::PropertyUnset;
        }
        else {
            $entry->type = Change\EntryType::PropertyChange;
        }

        $entry->property = $property;

        $this->entryController->setChange($entry, $previous, $current);

        $job->entries[] = $entry;
    }

    private function handleEntities(Job $job, array $entities, callable $processor): void
    {
        foreach ($entities as $entity) {
            if (str_starts_with($entity::class, 'Medas\\EntityChangeLog\\')) {
                continue;
            }

            if (attribute(LogChanges::class, new \ReflectionClass($entity))) {
                $processor($entity);

                $job->dispatchedEvents = true;
            }
        }
    }

    private function logDeletion(Job $job, $entity): void
    {
        $entry = $this->createEntry($job, $entity);

        $entry->type = Change\EntryType::EntityDeletion;
        $job->entries[] = $entry;
    }

    private function createEntry(Job $job, object $entity): Change\Entry
    {
        $entry = new Change\Entry();

        $entry->entity = $this->entityController->getChangeEntity($entity);
        $entry->entityId = $this->entityController->getEntityId($entity);

        dispatch(new Events\LoggingChange($entry, $entity));

        foreach ($this->listedUnder($job, $entity) as $listedEntity) {
            $job->listings[] = new Change\HistoryListing(
                $entry,
                $this->entityController->getChangeEntity($listedEntity),
                $this->entityController->getEntityId($listedEntity)
            );
        }

        return $entry;
    }

    /** @return object[] */
    private function listedUnder(Job $job, object $entity): array
    {
        if (!$job->listedUnder->offsetExists($entity)) {
            $job->listedUnder[$entity] = [$entity, ...$this->ownerResolver->owners($entity)];
        }

        return $job->listedUnder[$entity];
    }
}
