<?php

declare(strict_types=1);

namespace Medas\EntitiesChangelogTest\MockUps;

use Medas\EntityManager\Attributes\{Entity, Id, IsGeneratedValue};

#[Entity('projects')]
class Project
{
    #[Id, IsGeneratedValue]
    public int $id;

    public bool $isActive = true;
    public TaskCollection $tasks;

    public function __construct(
        public string $name,
    )
    {
    }
}
