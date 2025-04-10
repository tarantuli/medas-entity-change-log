<?php

declare(strict_types=1);

namespace Medas\EntityChangeLog;

use Medas\Core\AsSingleton;
use Medas\EntityEvents\EntityEventsPackage;
use Medas\ServiceManager\BasePackage;

class EntityChangeLogPackage extends BasePackage
{
    use AsSingleton;

    public function dependencies(): array
    {
        return [
            EntityEventsPackage::instance(),
        ];
    }

    public function sourceDirectory(): string
    {
        return __DIR__;
    }
}
