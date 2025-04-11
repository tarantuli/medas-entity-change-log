<?php

declare(strict_types=1);

namespace Medas\EntityChangeLog;

use Jfcherng\Diff\{DiffHelper, Renderer\RendererConstant};
use Medas\Core\Attributes\Service;
use Medas\EntityManager\Attributes\Changes\{DontLogChanges, LogChanges};
use Medas\EntityManager\Entities\{AfterFlushHandler, IdValue};
use Medas\EntityManager\Repository;
use Medas\EntityManager\Snapshots\Changes;
use Medas\StorageManager\Shared\ValueSerializer;

#[Service]
readonly class AfterChangeHandler implements AfterFlushHandler
{
    public function __construct(
        private IdValue         $idValue,
        private Repository      $repository,
        private ValueSerializer $valueSerializer,
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
            $previous = $this->valueSerializer->serialize($propertyChange->previous);
            $current = $this->valueSerializer->serialize($propertyChange->current);

            $diff = gzdeflate(DiffHelper::calculate($previous . "\n", $current . "\n", differOptions: [
                'context' => 1,
                'cliColorization' => RendererConstant::CLI_COLOR_DISABLE,
            ]));

            if (strlen($current) <= strlen($diff)) {
                $entry->changeType = Change\ChangeType::NewValue;
                $entry->change = $current;
            }
            else {
                $entry->changeType = Change\ChangeType::Diff;
                $entry->change = $diff;
            }

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
