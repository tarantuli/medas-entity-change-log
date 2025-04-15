<?php

declare(strict_types=1);

namespace Medas\EntityChangeLog;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\Attributes\Changes\{DontLogChanges, LogChanges};
use Medas\EntityManager\Entities\{AfterFlushHandler, IdValue};
use Medas\EntityManager\Repository;
use Medas\EntityManager\Snapshots\Changes;

#[Service]
readonly class AfterChangeHandler implements AfterFlushHandler
{
    public function __construct(
        private Change\EntryController $entryController,
        private IdValue                $idValue,
        private Repository             $repository,
    )
    {
    }

    public function handle(Changes $changes): bool
    {
        $job = $this->processChanges($changes);

        if ($job->dispatchedEvents) {
            em()->persist(...$job->entries);
        }

        return (bool) $job->changes;
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
        $entry = new Change\Entry();

        $entry->dateTime = new \DateTime();
        $entry->entity = $this->getChangeEntity($entity);
        $entry->entityId = (string) $this->idValue->fromEntity($entity);
        $entry->type = Change\EntryType::EntityCreation;
        $job->entries[] = $entry;
    }

    private function logChanges(Job $job, object $entity): void
    {
        foreach ($job->changes->entityChanges($entity) as $property => $propertyChange) {
            if (attribute(DontLogChanges::class, new \ReflectionProperty($entity, $property))) {
                continue;
            }

            $entry = new Change\Entry();

            $entry->dateTime = new \DateTime();
            $entry->entity = $this->getChangeEntity($entity);
            $entry->entityId = (string) $this->idValue->fromEntity($entity);
            $entry->type = Change\EntryType::PropertyChange;
            $entry->property = $this->getChangeProperty($property);

            $this->entryController->setChange(
                $entry,
                $propertyChange->previous,
                $propertyChange->current
            );

            $job->entries[] = $entry;
        }
    }

    private function getChangeProperty(string $name): mixed
    {
        return $this->repository->getOrCreate(
            Change\Property::class,
            ['nameHash' => sha1($name, true)],
            fn() => ['name' => $name],
            flushOnPersist: false
        );
    }

    private function handleEntities(Job $job, array $entities, callable $processor): void
    {
        foreach ($entities as $entity) {
            if (str_starts_with($entity::class, 'Medas\EntityChangeLog\\')) {
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
        $entry = new Change\Entry();

        $entry->dateTime = new \DateTime();
        $entry->entity = $this->getChangeEntity($entity);
        $entry->entityId = (string) $this->idValue->fromEntity($entity);
        $entry->type = Change\EntryType::EntityDeletion;
        $job->entries[] = $entry;
    }

    private function getChangeEntity(object $entity): mixed
    {
        return $this->repository->getOrCreate(
            Change\Entity::class,
            ['nameHash' => sha1($entity::class, true)],
            fn() => ['name' => $entity::class],
            flushOnPersist: false
        );
    }
}
