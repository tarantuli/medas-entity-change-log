<?php

declare(strict_types=1);

namespace Medas\EntitiesChangelogTest\MockUps;

use Medas\EntitiesChangelog\Attributes\LogChanges;
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
