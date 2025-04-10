<?php

declare(strict_types=1);

use Medas\ConfigManager\ConfigManagerPackage;
use Medas\EntityChangeLog\EntityChangeLogPackage;
use Medas\JsonStorage\JsonStoragePackage;
use Medas\JsonStorage\StorageDirectory;
use Medas\RamseyUuidBridge\RamseyUuidBridgePackage;
use Medas\ServiceManager\{ServiceConfig, ServiceManager};
use Medas\StorageManager\StorageManager;

chdir(__DIR__);

new ServiceManager(function (): ServiceConfig {
    $config = new ServiceConfig();

    $config->addPackages([
        EntityChangeLogPackage::instance(),
        JsonStoragePackage::instance(),
        RamseyUuidBridgePackage::instance(),
        ConfigManagerPackage::instance(),
    ]);

    return $config;
});

\service(StorageManager::class)->add(
    new StorageDirectory(__DIR__ . '/tests/Storage')
);
