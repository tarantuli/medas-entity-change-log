<?php

declare(strict_types=1);

namespace Medas\EntityChangeLog\ConfigOptions;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption};

#[Service]
readonly class EntityNamesStore implements ConfigOption
{
    public function __construct(
        private EntityChangeLogGroup $group,
    )
    {
    }

    public function group(): ConfigGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'entity-names-store';
    }

    public function description(): string
    {
        return 'The name of the store where entity change log entity names will be stored';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): string
    {
        return 'entity_changelog_entities';
    }
}
