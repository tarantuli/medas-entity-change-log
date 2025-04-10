<?php

declare(strict_types=1);

namespace Medas\EntityChangeLog\ConfigOptions;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption};
use Medas\EntityManager\ConfigOptions\StoreNamesGroup;

#[Service]
readonly class ChangeLogStorage implements ConfigOption
{
    public function __construct(
        private StoreNamesGroup $group,
    )
    {
    }

    public function group(): ConfigGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'entity-change-log-storage';
    }

    public function description(): string
    {
        return 'The name of the storage where entity change logs will be stored';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): null
    {
        return null;
    }
}
