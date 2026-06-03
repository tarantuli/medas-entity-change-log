<?php

declare(strict_types=1);

namespace Medas\EntityChangeLog\Change;

use Medas\Core\{Interfaces\HasId, Interfaces\Uuid, Types\Binary};
use Medas\EntityChangeLog\Change\Entity as ChangeEntity;
use Medas\EntityChangeLog\ConfigOptions\{EntriesStore, StorageName};
use Medas\EntityManager\Attributes\{Entity, Id, IsCreationTimestamp};

#[Entity, Entity\StorageConfigOption(StorageName::class), Entity\StoreConfigOption(EntriesStore::class)]
class Entry implements HasId
{
    #[Id]
    public Uuid $id;

    #[IsCreationTimestamp]
    public \DateTime $dateTime;

    public ChangeEntity $entity;
    public string $entityId;
    public EntryType $type;
    public string|null $property;

    #[Binary(maxLength: Binary::MAX_2_BYTE_LENGTH)]
    public string|null $change;

    public function id(): Uuid
    {
        return $this->id;
    }
}
