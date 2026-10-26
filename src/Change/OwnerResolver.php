<?php

declare(strict_types=1);

namespace Medas\EntityChangeLog\Change;

use Medas\Core\{Attributes\Service, Interfaces\CacheManager};
use Medas\EntityManager\Attributes\Changes\LogChangesTo;

/**
 * Finds the entities a change is also listed under: the relations marked LogChangesTo,
 * followed up to the top.
 */
#[Service]
readonly class OwnerResolver
{
    public function __construct(
        private CacheManager $cacheManager,
    )
    {
    }

    /** @return object[] */
    public function owners(object $entity): array
    {
        $owners = [];
        $visited = new \SplObjectStorage();

        $visited->offsetSet($entity);

        $this->collectOwners($entity, $owners, $visited);

        return $owners;
    }

    private function collectOwners(object $entity, array &$owners, \SplObjectStorage $visited): void
    {
        foreach ($this->ownerProperties($entity::class) as $propertyName) {
            $property = new \ReflectionProperty($entity, $propertyName);

            if (!$property->isInitialized($entity)) {
                continue;
            }

            $owner = $property->getValue($entity);

            // Guards against two entities marked towards each other
            if ($owner === null || $visited->offsetExists($owner)) {
                continue;
            }

            $visited->offsetSet($owner);

            $owners[] = $owner;

            $this->collectOwners($owner, $owners, $visited);
        }
    }

    /** @return string[] */
    private function ownerProperties(string $class): array
    {
        return $this->cacheManager->get('memory')->get(
            ['entity-change-log', 'owner-properties', $class],
            fn()
                => array_values(array_map(fn(\ReflectionProperty $property) => $property->getName(), array_filter(
                    new \ReflectionClass($class)->getProperties(), fn(\ReflectionProperty $property)
                        => $property->getAttributes(LogChangesTo::class)
                    )
                )
            )
        );
    }
}
