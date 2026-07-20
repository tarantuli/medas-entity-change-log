<?php

declare(strict_types=1);

namespace Medas\EntityChangeLog\Changelog;

use Medas\Core\Attributes\Service;
use Medas\EntityChangeLog\Change\{Entity, Entry};
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
        Entity $entity,
        string $id,
    )
    {
        $this->definition = new Definition($this->entity())
            ->add(
                WhereIs::c(Property::c('entity'), Value::c($entity->id())),
                WhereIs::c(Property::c('id'), Value::c($id)),
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
