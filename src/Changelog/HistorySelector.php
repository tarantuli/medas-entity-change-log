<?php

declare(strict_types=1);

namespace Medas\EntityChangeLog\Changelog;

use Medas\Core\Attributes\Service;
use Medas\EntityChangeLog\Change\Entry;
use Medas\EntityManager\Selector\{
    Conditions\WhereIs,
    Definition,
    Operants\Property,
    Operants\Value,
    Selector
};

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
                WhereIs::c(Property::c('entity'), Value::c($entity)),
                WhereIs::c(Property::c('entityId'), Value::c($id)),
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
