<?php

declare(strict_types=1);

namespace Medas\EntitiesChangelog;

use Medas\Core\AsSingleton;
use Medas\ServiceManager\BasePackage;

class EntitiesChangelogPackage extends BasePackage
{
    use AsSingleton;

    public function dependencies(): array
    {
        return [];
    }

    public function sourceDirectory(): string
    {
        return __DIR__;
    }
}
