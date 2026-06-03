# medas-entity-change-log

Part of the [Medas framework](https://github.com/tarantuli/medas-core).

## Description

Automatically records a structured audit trail for entity changes managed by `medas-entity-manager`. After each flush, `AfterChangeHandler` inspects the `Changes` snapshot and writes `Change\Entry` records for every entity class annotated with `#[LogChanges]`.

Three kinds of events are recorded:

| `EntryType`      | When                                                        |
|------------------|-------------------------------------------------------------|
| `EntityCreation` | A new entity is flushed for the first time                  |
| `PropertyChange` | A tracked property value differs from its previous snapshot |
| `EntityDeletion` | An entity is deleted                                        |

When an entity is created, one `EntityCreation` entry is written followed by a `PropertyChange` entry for each initial property value — giving a complete picture of the initial state without requiring a separate "before" snapshot.

For `PropertyChange` entries the old and new values are each serialised to pretty-printed JSON, diffed using `jfcherng/php-diff`, and the resulting unified diff is gzip-deflated before storage in a `BLOB` column. Diffs that exceed the 2-byte-length blob limit are replaced with the string `'too large to store'`. The number of context lines around each changed block is configurable.

Individual properties can be excluded from logging by annotating them with `#[DontLogChanges]`.

The package registers two storage entities (`Change\Entity` and `Change\Entry`) via `PackageEntities`, both pointing to the storage and store names defined by their respective config options.

**`Change\Entry` fields:**

| Field          | Type            | Description                                             |
|----------------|-----------------|---------------------------------------------------------|
| `id`           | `Uuid`          | Primary key                                             |
| `dateTime`     | `DateTime`      | Set automatically on creation                           |
| `entity`       | `Change\Entity` | Reference to the deduplicated entity class name         |
| `entityId`     | `string`        | String representation of the logged entity's id         |
| `type`         | `EntryType`     | `EntityCreation`, `PropertyChange`, or `EntityDeletion` |
| `property`     | `string\|null`  | Property name for `PropertyChange` entries              |
| `change`       | `string\|null`  | Gzip-deflated unified diff for `PropertyChange` entries |

## Configuration options

| Option                                                    | Default                      | Description                                      |
|-----------------------------------------------------------|------------------------------|--------------------------------------------------|
| `store-names.entity-change-log.storage-name`              | `null` (default storage)     | Storage backend for all change-log stores        |
| `store-names.entity-change-log.entries-store`             | `entity-change-log-changes`  | Store/table name for change entries              |
| `store-names.entity-change-log.entity-names-store`        | `entity-change-log-entities` | Store/table name for entity name records         |
| `store-names.entity-change-log.diff-context-lines`        | `1`                          | Context lines around each change in stored diffs |

## Usage

### Package developer context

Register the package and annotate entities whose changes should be tracked:

```php
use Medas\EntityChangeLog\EntityChangeLogPackage;

EntityChangeLogPackage::instance();
```

**Opting an entity into change logging:**

```php
use Medas\EntityManager\Attributes\{Entity, Id, Changes\LogChanges};
use Medas\Core\Interfaces\{HasId, Uuid};

#[Entity, LogChanges]
class Invoice implements HasId
{
    #[Id]
    public Uuid $id;

    public string $status;
    public int $amountCents;
    public \DateTime $updatedAt;

    public function id(): Uuid
    {
        return $this->id;
    }
}
```

Every time an `Invoice` is created, updated, or deleted and the entity manager flushes, `AfterChangeHandler` automatically writes the corresponding log entries. No additional wiring is needed beyond registering the package.

**Excluding a property from logging:**

```php
use Medas\EntityManager\Attributes\Changes\DontLogChanges;

#[Entity, LogChanges]
class Invoice implements HasId
{
    #[Id]
    public Uuid $id;

    public string $status;

    // This property changes on every request and would flood the log
    #[DontLogChanges]
    public \DateTime $lastAccessedAt;
}
```

**Reading the stored diff for a property change:**

```php
use Medas\EntityChangeLog\Change\{Entry, EntryController, EntryType};
use Medas\Core\Attributes\Service;

#[Service]
readonly class ChangeLogViewer
{
    public function __construct(
        private EntryController $entryController,
    ) {}

    public function getDiff(Entry $entry): string|null
    {
        if ($entry->type !== EntryType::PropertyChange || $entry->change === null) {
            return null;
        }

        // Decompresses the gzip-deflated unified diff string
        return $this->entryController->getChange($entry);
    }
}
```

**Processing change snapshots programmatically** (e.g., in tests or migrations):

```php
use Medas\EntityChangeLog\AfterChangeHandler;
use Medas\EntityManager\Snapshots\Changes;

$job = $afterChangeHandler->processChanges($changes);

foreach ($job->entries as $entry) {
    echo $entry->type->name . ' on ' . $entry->entityId . "\n";
}
```

`processChanges()` builds the entries without persisting them. `handle()` calls `processChanges()` and then persists if any events were dispatched.

### Backend user context

Change logging is entirely automatic once the package is registered and entities are annotated with `#[LogChanges]`. The log entries can be queried through the entity manager like any other entity:

```php
use Medas\EntityChangeLog\Change\{Entry, EntryType};
use Medas\EntityManager\Repository;

// Fetch all change entries for a specific entity instance
$entries = $repository->findBy(Entry::class, [
    'entityId' => (string) $invoice->id(),
]);

// Filter to property changes only
$propertyChanges = array_filter(
    $entries,
    fn(Entry $e) => $e->type === EntryType::PropertyChange,
);
```

**Configuring storage via YAML:**

To write change logs to a dedicated database connection rather than the default one:

```yaml
store-names:
  entity-change-log:
    storage-name: audit-db
    entries-store: change_log_entries
    entity-names-store: change_log_entities
    diff-context-lines: 3
```

**Diff format** — stored diffs are unified-diff strings compressed with `gzdeflate`. To display them, retrieve via `EntryController::getChange()` which calls `gzinflate()` and returns the raw diff text. Values that were too large to diff are stored as the literal string `'too large to store'`.
