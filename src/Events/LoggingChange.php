<?php

declare(strict_types=1);

namespace Medas\EntityChangeLog\Events;

use Medas\Core\Attributes\Service;
use Medas\EntityChangeLog\Change\Entry;

#[Service]
readonly class LoggingChange
{
    public function __construct(
        public Entry  $entry,
        public object $entity,
    )
    {
    }
}
