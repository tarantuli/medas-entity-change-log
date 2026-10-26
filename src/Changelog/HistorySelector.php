<?php

declare(strict_types=1);

namespace Medas\EntityChangeLog\Changelog;

use Medas\Core\Attributes\Service;
use Medas\EntityChangeLog\Change\{Entry, HistoryListing};
use Medas\EntityManager\Selector\{
    Conditions\WhereIs,
    Conditions\WhereReferencedBy,
    Definition,
    Operants\Property,
    Operants\Value,
    Selector,
    Sorting\SortBy
};

/**
 * The history of one entity: its own changes plus those of the entities that log their
 * changes to it, oldest first.
 */
#[Service]
readonly class HistorySelector implements Selector
{
    private Definition $definition;

    public function __construct(
        string $entity,
        string $id,
    )
    {
        $this->definition = new Definition($this->entity())
            ->add(
                WhereReferencedBy::c(
                    HistoryListing::class,
                    'entry',
                    'id',
                    WhereIs::c(Property::c('entity', HistoryListing::class), Value::c($entity)),
                    WhereIs::c(Property::c('entityId', HistoryListing::class), Value::c($id)),
                ),
                SortBy::c(Property::c('createdAt')),

                // Entry ids are time-ordered, keeping the order of changes made in one flush
                SortBy::c(Property::c('id')),
            );
    }

    public function definition(): Definition
    {
        return $this->definition;
    }

    public function entity(): string
    {
        return Entry::class;
    }
}
