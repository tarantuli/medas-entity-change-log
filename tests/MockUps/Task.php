<?php

declare(strict_types=1);

namespace Medas\EntitiesChangeLogTest\MockUps;

use Medas\EntitiesChangeLog\Attributes\LogChanges;
use Medas\EntityManager\Attributes\{Entity, Id, IsGeneratedValue};

#[Entity('tasks'), LogChanges]
class Task
{
    #[Id, IsGeneratedValue]
    public int $id;

    public bool $isActive = true;

    public function __construct(
        public string $description,
    )
    {
    }
}
