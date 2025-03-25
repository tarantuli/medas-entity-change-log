<?php

declare(strict_types=1);

namespace Medas\EntitiesChangeLog\ConfigOptions;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption};
use Medas\EntityManager\ConfigOptions\StoreNamesGroup;

#[Service]
readonly class PropertyNamesStore implements ConfigOption
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
        return 'entity-change-log-property-names';
    }

    public function description(): string
    {
        return 'The name of the store where entity change log property names will be stored';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): string
    {
        return 'entity-change-log-properties';
    }
}
