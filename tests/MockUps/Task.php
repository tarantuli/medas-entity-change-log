<?php

declare(strict_types=1);

namespace Medas\EntityChangeLogTest\MockUps;

use Medas\Core\Interfaces\{Guid,HasId};
use Medas\EntityManager\Attributes\{Entity,Id};
use Medas\EntityManager\Attributes\Changes\LogChanges;

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
