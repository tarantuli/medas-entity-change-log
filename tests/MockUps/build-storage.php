<?php

declare(strict_types=1);

use Medas\JsonStorage\Actions\CreateStore\CreateStore;
use Medas\StorageManager\Migrations\ActionGatherer;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../phpunit.bootstrap.php';

$targetDirectory = __DIR__ . '/../Storage';

foreach (glob($targetDirectory . '/*.json') as $file) {
    unlink($file);
}

$actions = service(ActionGatherer::class)->gather([__DIR__, __DIR__ . '/../../src']);

foreach ($actions as $action) {
    /** @var CreateStore $action */
    file_put_contents($action->path, $action->content);
}
