<?php

declare(strict_types=1);

namespace Medas\EntitiesChangeLog\Change;

use Medas\Core\Interfaces\HasId;
use Medas\EntitiesChangeLog\ConfigOptions\{ChangeLogStorage, PropertyNamesStore};
use Medas\EntityManager\Attributes\{
    Entity as EntityAttribute,
    Entity\StorageConfigOption,
    Entity\StoreConfigOption,
    Id,
    IsGeneratedValue,
    IsUnique
};
use Medas\EntityManager\Types\Binary;

#[EntityAttribute, StorageConfigOption(ChangeLogStorage::class), StoreConfigOption(PropertyNamesStore::class)]
class Property implements HasId
{
    #[Id, IsGeneratedValue]
    public int $id;

    #[IsUnique, Binary(length: 20)]
    public string $nameHash;

    public string $name;

    public function id(): int
    {
        return $this->id;
    }
}
