<?php

declare(strict_types=1);

namespace Medas\EntityChangeLog\Change;

use Medas\Core\{Interfaces\HasId, Interfaces\Uuid, Types\Binary};
use Medas\EntityChangeLog\ConfigOptions\{EntriesStore, StorageName};
use Medas\EntityManager\{Attributes\Entity as EntityAttr, Attributes\Id, Traits\Timestamps};

#[EntityAttr, EntityAttr\StorageConfigOption(StorageName::class), EntityAttr\StoreConfigOption(EntriesStore::class)]
class Entry implements HasId
{
    use Timestamps;

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

    public function id(): Uuid
    {
        return $this->id;
    }
}
