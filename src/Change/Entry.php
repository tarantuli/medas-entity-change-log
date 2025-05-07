<?php

declare(strict_types=1);

namespace Medas\EntityChangeLog\Change;

use Medas\Core\Interfaces\{HasId, Uuid};
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
    public Uuid $id;

    #[IsCreationTimestamp]
    public \DateTime $dateTime;

    public ChangEntity $entity;
    public string $entityId;
    public EntryType $type;
    public string|null $property;

    #[Binary(maxLength: Binary::MAX_2_BYTE_LENGTH)]
    public string|null $change;

    public string|null $connectionId;

    public function id(): Uuid
    {
        return $this->id;
    }
}
