<?php

declare(strict_types=1);

use Medas\EntitiesChangelog\EntitiesChangelogPackage;
use Medas\ServiceManager\{ServiceConfig, ServiceManager};

chdir(__DIR__);

new ServiceManager(function (): ServiceConfig {
    $config = new ServiceConfig();

    $config->addPackages([
        EntitiesChangelogPackage::instance(),
    ]);

    return $config;
});
