<?php

declare(strict_types=1);

namespace Medas\EntitiesChangelog;

use Medas\EntityManager\Entities\Changes;

class Job
{
    public bool $dispatchedEvents = false;

    /** @var Change\Entry[] */
    public array $entries = [];

    public function __construct(
        public Changes $changes,
    )
    {
    }
}
