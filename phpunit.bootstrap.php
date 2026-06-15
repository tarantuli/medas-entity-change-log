<?php

declare(strict_types=1);

use Medas\ConfigManager\ConfigManagerPackage;
use Medas\EntityChangeLog\EntityChangeLogPackage;
use Medas\JsonStorage\{JsonStoragePackage, StorageDirectory};
use Medas\ObjectInstantiator\ObjectInstantiator;
use Medas\RamseyUuidBridge\RamseyUuidBridgePackage;
use Medas\ServiceManager\{ServiceConfigBuilder, ServiceManager};
use Medas\StorageManager\StorageManager;

chdir(__DIR__);

new ServiceManager(function (): ServiceConfigBuilder {
    $config = new ServiceConfigBuilder(ObjectInstantiator::class);

    $config->addPackages([
        EntityChangeLogPackage::instance(),
        JsonStoragePackage::instance(),
        RamseyUuidBridgePackage::instance(),
        ConfigManagerPackage::instance(),
    ]);

    return $config;
});

\service(StorageManager::class)->add(new StorageDirectory(__DIR__ . '/tests/Storage'));
