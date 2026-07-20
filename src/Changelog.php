<?php

declare(strict_types=1);

namespace Medas\EntityChangeLog;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\{Entities\IdValue, Selector\Selector};

#[Service]
readonly class Changelog
{
    public function __construct(
        private Change\EntryController $entryController,
        private IdValue                $idValue,
    )
    {
    }

    public function createSelector(object $entity): Selector
    {
        return new Changelog\HistorySelector(
            $this->entryController->getChangeEntity($entity),
            (string) $this->idValue->fromEntity($entity)
        );
    }
}
