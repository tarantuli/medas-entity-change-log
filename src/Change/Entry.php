<?php

declare(strict_types=1);

namespace Medas\EntityChangeLog\Change;

use Medas\Core\{Interfaces\HasId, Interfaces\Uuid, Types\Binary};
use Medas\EntityChangeLog\ConfigOptions\{EntriesStore, StorageName};
use Medas\EntityManager\Attributes\{
    Changes\DontLogChanges,
    Entity as EntityAttr,
    Id,
    IsCreationTimestamp
};

#[EntityAttr, EntityAttr\StorageConfigOption(StorageName::class), EntityAttr\StoreConfigOption(EntriesStore::class)]
class Entry implements HasId
{
    #[Id]
    public Uuid $id;

    public Entity $entity;

    #[Binary]
    public string $entityId;

    public EntryType $type;
    public string|null $property;

    #[Binary(maxLength: Binary::MAX_2_BYTE_LENGTH)]
    public string|null $change;

    public string|null $user;

    #[IsCreationTimestamp, DontLogChanges]
    public \DateTime $createdAt;

    public function createdAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function id(): Uuid
    {
        return $this->id;
    }
}
