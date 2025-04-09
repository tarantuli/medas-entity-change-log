<?php

declare(strict_types=1);

namespace Medas\EntitiesChangeLog\Change;

use Medas\Core\Interfaces\{Guid, HasId};
use Medas\EntitiesChangeLog\{
    Change\Entity as ChangEntity,
    ConfigOptions\ChangeLogStorage,
    ConfigOptions\EntriesStoreName
};
use Medas\EntityManager\Attributes\{Entity, Id, IsCreationTimestamp};

#[Entity, Entity\StorageConfigOption(ChangeLogStorage::class), Entity\StoreConfigOption(EntriesStoreName::class)]
class Entry implements HasId
{
    #[Id]
    public Guid $id;

    #[IsCreationTimestamp]
    public \DateTime $dateTime;

    public ChangEntity $entity;
    public string $entityId;
    public Type $type;
    public Property|null $property;
    public string|null $connectionId;

    public function id(): Guid
    {
        return $this->id;
    }
}
