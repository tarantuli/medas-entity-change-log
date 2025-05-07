<?php

declare(strict_types=1);

namespace Medas\EntityChangeLogTest\MockUps;

use Medas\Core\Interfaces\{HasId, Uuid};
use Medas\EntityManager\Attributes\{Changes\LogChanges, Entity, Id};

#[Entity('tasks'), LogChanges]
class Task implements HasId
{
    #[Id]
    public Uuid $id;

    public bool $isActive = true;

    public function __construct(
        public string $description,
    )
    {
    }

    public function id(): Uuid
    {
        return $this->id;
    }
}
