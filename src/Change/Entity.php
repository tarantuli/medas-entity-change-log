<?php

declare(strict_types=1);

namespace Medas\EntityChangeLog\Change;

use Medas\Core\Interfaces\HasId;
use Medas\EntityChangeLog\ConfigOptions\{ChangeLogStorage, EntityNamesStore};
use Medas\EntityManager\Attributes\{
    Entity as EntityAttribute,
    Entity\StorageConfigOption,
    Entity\StoreConfigOption,
    Id
};
use Medas\EntityManager\Types\Binary;

#[EntityAttribute, StorageConfigOption(ChangeLogStorage::class), StoreConfigOption(EntityNamesStore::class)]
class Entity implements HasId
{
    #[Id, Binary(length: 20)]
    public string $nameHash;

    public string $name;

    public function id(): string
    {
        return $this->nameHash;
    }
}
