<?php

declare(strict_types=1);

namespace Medas\EntitiesChangelog\Change;

use Medas\EntitiesChangelog\ConfigOptions\{ChangelogStorage, ChangePropertyName};
use Medas\EntityManager\Attributes\{
    Entity as EntityAttribute,
    Entity\StorageConfigOption,
    Entity\StoreConfigOption,
    Id,
    IsGeneratedValue,
    IsUnique
};
use Medas\EntityManager\Types\Binary;

#[EntityAttribute, StorageConfigOption(ChangelogStorage::class), StoreConfigOption(ChangePropertyName::class)]
class Property
{
    #[Id, IsGeneratedValue]
    public int $id;

    #[IsUnique, Binary(length: 20)]
    public string $nameHash;

    public string $name;
}
