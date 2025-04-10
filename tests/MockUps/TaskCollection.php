<?php

declare(strict_types=1);

namespace Medas\EntityChangeLogTest\MockUps;

use Medas\EntityManager\Attributes\EntityCollection;
use Medas\StorageManager\Entities\RecordCollection;

/**
 * @extends RecordCollection<Task>
 */
#[EntityCollection(Task::class)]
class TaskCollection extends RecordCollection
{
}
