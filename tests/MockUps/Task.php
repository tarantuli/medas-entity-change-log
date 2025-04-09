<?php

declare(strict_types=1);

namespace Medas\EntitiesChangeLogTest\MockUps;

use Medas\Core\Interfaces\{Guid, HasId};
use Medas\EntitiesChangeLog\Attributes\LogChanges;
use Medas\EntityManager\Attributes\{Entity, Id};

#[Entity('tasks'), LogChanges]
class Task implements HasId
{
    #[Id]
    public Guid $id;

    public bool $isActive = true;

    public function __construct(
        public string $description,
    )
    {
    }

    public function id(): Guid
    {
        return $this->id;
    }
}
