<?php

declare(strict_types=1);

namespace Medas\EntitiesChangelog\ConfigOptions;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption};
use Medas\EntityManager\ConfigOptions\StoreNamesGroup;

#[Service]
readonly class ChangeStoreName implements ConfigOption
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
        return 'entity-changelog-entries';
    }

    public function description(): string
    {
        return 'The name of the store where entity changelog changes will be stored';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): string
    {
        return 'entity-changelog-changes';
    }
}
