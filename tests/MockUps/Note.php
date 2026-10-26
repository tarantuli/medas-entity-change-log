<?php

declare(strict_types=1);

namespace Medas\EntityChangeLogTest\MockUps;

use Medas\Core\Interfaces\{HasId, Uuid};
use Medas\EntityManager\Attributes\{Changes\LogChanges, Changes\LogChangesTo, Entity, Id};

#[Entity('notes'), LogChanges]
class Note implements HasId
{
    #[Id]
    public Uuid $id;

    public function __construct(
        #[LogChangesTo]
        public Task|null $task,
        public string    $text,
    )
    {
    }

    public function id(): Uuid
    {
        return $this->id;
    }
}
