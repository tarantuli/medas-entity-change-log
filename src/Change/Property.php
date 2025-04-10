<?php

declare(strict_types=1);

namespace Medas\EntityChangeLog\Change;

use Medas\Core\Interfaces\{Guid, HasId};
use Medas\EntityChangeLog\ConfigOptions\{ChangeLogStorage, PropertyNamesStore};
use Medas\EntityManager\Attributes\{
    Entity as EntityAttribute,
    Entity\StorageConfigOption,
    Entity\StoreConfigOption,
    Id,
    IsUnique
};
use Medas\EntityManager\Types\Binary;

#[EntityAttribute, StorageConfigOption(ChangeLogStorage::class), StoreConfigOption(PropertyNamesStore::class)]
class Property implements HasId
{
    #[Id]
    public Guid $id;

    #[IsUnique, Binary(length: 20)]
    public string $nameHash;

    public string $name;

    public function id(): Guid
    {
        return $this->id;
    }
}
