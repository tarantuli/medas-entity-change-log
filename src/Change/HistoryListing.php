<?php

declare(strict_types=1);

namespace Medas\EntityChangeLog\Change;

use Medas\Core\{Interfaces\HasId, Interfaces\Uuid, Types\Binary};
use Medas\EntityChangeLog\ConfigOptions\{ListingsStore, StorageName};
use Medas\EntityManager\Attributes\{CompoundIndex, Entity as EntityAttr, Id};

/**
 * Lists an entry in the history of one entity: the entity that changed, and every
 * entity it logs its changes to (see LogChangesTo). A history is read through these
 * rows only, so the changed entity gets one as well.
 */
#[EntityAttr, EntityAttr\StorageConfigOption(StorageName::class), EntityAttr\StoreConfigOption(ListingsStore::class)]
#[CompoundIndex('entity', 'entityId')]
class HistoryListing implements HasId
{
    #[Id]
    public Uuid $id;

    public function __construct(
        public Entry  $entry,
        public Entity $entity,

        #[Binary]
        public string $entityId,
    )
    {
    }

    public function id(): Uuid
    {
        return $this->id;
    }
}
