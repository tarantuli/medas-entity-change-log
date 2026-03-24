<?php

declare(strict_types=1);

namespace Medas\EntityChangeLog\ConfigOptions;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption};
use Medas\EntityManager\ConfigOptions\StoreNamesGroup;

#[Service]
readonly class DiffContextLines implements ConfigOption
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
        return 'entity-change-log-diff-context-lines';
    }

    public function description(): string
    {
        return 'Number of surrounding lines of context to include around each change in stored diffs';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): int
    {
        return 1;
    }
}
