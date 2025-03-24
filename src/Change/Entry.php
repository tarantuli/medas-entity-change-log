<?php

declare(strict_types=1);

namespace Medas\EntitiesChangelog\Change;

use Medas\EntitiesChangelog\{
    Change\Entity as ChangEntity,
    ConfigOptions\ChangelogStorage,
    ConfigOptions\ChangeStoreName
};
use Medas\EntityManager\Attributes\{Entity, Id, IsCreationTimestamp, IsGeneratedValue};

#[Entity, Entity\StorageConfigOption(ChangelogStorage::class), Entity\StoreConfigOption(ChangeStoreName::class)]
class Entry
{
    #[Id, IsGeneratedValue]
    public int $id;

    #[IsCreationTimestamp]
    public \DateTime $dateTime;

    public ChangEntity $entity;
    public string $entityId;
    public Type $type;
    public Property|null $property;
    public string|null $connectionId;
}
