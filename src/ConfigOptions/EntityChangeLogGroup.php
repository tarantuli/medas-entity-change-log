<?php

declare(strict_types=1);

namespace Medas\EntityChangeLog\ConfigOptions;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup};
use Medas\EntityManager\ConfigOptions\StoreNamesGroup;

#[Service]
readonly class EntityChangeLogGroup implements ConfigGroup
{
    public function __construct(
        private StoreNamesGroup $group,
    )
    {
    }

    public function parent(): ConfigGroup|null
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'entity-change-log';
    }
}
