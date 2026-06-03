<?php

declare(strict_types=1);

namespace Medas\EntityChangeLog\Change;

use Medas\Core\{Interfaces\HasId, Types\Binary};
use Medas\EntityChangeLog\ConfigOptions\{EntityNamesStore, StorageName};
use Medas\EntityManager\Attributes\{
    Entity as EntityAttribute,
    Entity\StorageConfigOption,
    Entity\StoreConfigOption,
    Id
};

#[EntityAttribute, StorageConfigOption(StorageName::class), StoreConfigOption(EntityNamesStore::class)]
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
