<?php

declare(strict_types=1);

namespace Medas\EntityChangeLog\Change;

use Medas\Core\{Attributes\Service, Interfaces\Uuid};
use Medas\EntityChangeLog\Changelog\HistorySelector;
use Medas\EntityManager\{Entities\IdValue, Repository, Selector\Selector};

#[Service]
readonly class EntityController
{
    public function __construct(
        private IdValue    $idValue,
        private Repository $repository,
    )
    {
    }

    public function getChangeEntity(object $entity): Entity
    {
        return $this->repository->getOrCreate(
            Entity::class,
            ['nameHash' => sha1($entity::class, true)],
            fn() => ['name' => $entity::class]
        );
    }

    public function getEntityId(object $entity): string
    {
        $id = $this->idValue->fromEntity($entity);

        if ($id instanceof Uuid) {
            return $id->toBytes();
        }

        return (string) $id;
    }

    public function createSelector(object $entity): Selector
    {
        return new HistorySelector($this->getChangeEntity($entity), $this->getEntityId($entity));
    }
}
