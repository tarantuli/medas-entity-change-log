<?php

declare(strict_types=1);

namespace Medas\EntityChangeLog\Change;

use Medas\Core\Interfaces\{Guid, HasId};
use Medas\EntityChangeLog\{
    Change\Entity as ChangEntity,
    ConfigOptions\ChangeLogStorage,
    ConfigOptions\EntriesStoreName
};
use Medas\EntityManager\{
    Attributes\Entity,
    Attributes\Id,
    Attributes\IsCreationTimestamp,
    Types\Binary
};

#[Entity, Entity\StorageConfigOption(ChangeLogStorage::class), Entity\StoreConfigOption(EntriesStoreName::class)]
class Entry implements HasId
{
    #[Id]
    public Guid $id;

    #[IsCreationTimestamp]
    public \DateTime $dateTime;

    public ChangEntity $entity;
    public string $entityId;
    public EntryType $type;
    public Property|null $property;
    public ChangeType|null $changeType;

    #[Binary(maxLength: Binary::MAX_2_BYTE_LENGTH)]
    public string|null $change;

    public string|null $connectionId;

    public function id(): Guid
    {
        return $this->id;
    }
}
