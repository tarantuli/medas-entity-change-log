<?php

declare(strict_types=1);

namespace Medas\EntitiesChangelog;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\{
    Entities\AfterFlushHandler,
    Entities\Changes,
    Entities\IdValue,
    Repository
};

#[Service]
readonly class AfterChangeHandler implements AfterFlushHandler
{
    public function __construct(
        private IdValue    $idValue,
        private Repository $repository,
    )
    {
    }

    public function handle(Changes $changes): bool
    {
        $job = $this->processChanges($changes);

        em()->persist(...$job->entries);
        em()->flush();

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
            fn($entity) => $this->logCreation($job, $entity)
        );

        return $job;
    }

    private function logChanges(Job $job, object $entity): void
    {
        foreach ($job->changes->entityChanges($entity) as $property => $entry) {
            $entry = new Change\Entry();

            $entry->dateTime = new \DateTime();
            $entry->entity = $this->getChangeEntity($entity);
            $entry->entityId = (string) $this->idValue->fromEntity($entity);
            $entry->type = Change\Type::PropertyChange;
            $entry->property = $this->getChangeProperty($property);
            $job->entries[] = $entry;
        }
    }

    private function getChangeProperty(string $name): mixed
    {
        return $this->repository->getOrCreate(
            Change\Property::class,
            ['nameHash' => sha1($name, true)],
            fn() => ['name' => $name]
        );
    }

    private function handleEntities(Job $job, array $entities, callable $processor): void
    {
        foreach ($entities as $entity) {
            if (attribute(Attributes\LogChanges::class, new \ReflectionClass($entity))) {
                $processor($entity);

                $job->dispatchedEvents = true;
            }
        }
    }

    private function logCreation(Job $job, object $entity): void
    {
        $entry = new Change\Entry();

        $entry->dateTime = new \DateTime();
        $entry->entity = $this->getChangeEntity($entity);
        $entry->entityId = (string) $this->idValue->fromEntity($entity);
        $entry->type = Change\Type::EntityCreation;
        $job->entries[] = $entry;
    }

    private function getChangeEntity(object $entity): mixed
    {
        return $this->repository->getOrCreate(
            Change\Entity::class,
            ['nameHash' => sha1($entity::class, true)],
            fn() => ['name' => $entity::class]
        );
    }
}
