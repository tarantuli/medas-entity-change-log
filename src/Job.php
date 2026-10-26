<?php

declare(strict_types=1);

namespace Medas\EntityChangeLog;

use Medas\EntityManager\Snapshots\Changes;

class Job
{
    public bool $dispatchedEvents = false;

    /** @var Change\Entry[] */
    public array $entries = [];

    /** @var Change\HistoryListing[] */
    public array $listings = [];

    /**
     * The entities each changed entity is listed under (itself first), resolved once
     * per entity rather than for every changed property.
     */
    public \SplObjectStorage $listedUnder;

    public function __construct(
        public Changes $changes,
    )
    {
        $this->listedUnder = new \SplObjectStorage();
    }
}
