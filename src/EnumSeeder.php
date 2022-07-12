<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2022 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\EnumSeeder;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Seeder;

/**
 * EnumSeeder allows synchronization for the dictionary (enum) data per single database table.
 *
 * Usage example:
 *
 * ```
 * use Illuminatech\EnumSeeder\EnumSeeder;
 *
 * class ItemCategorySeeder extends EnumSeeder
 * {
 *     protected function table(): string
 *     {
 *         return 'item_statuses';
 *     }
 *
 *     protected function rows(): array
 *     {
 *         return [
 *             [
 *                 'id' => 1,
 *                 'name' => 'Pending',
 *             ],
 *             [
 *                 'id' => 2,
 *                 'name' => 'Active',
 *             ],
 *             // ...
 *         ];
 *     }
 * }
 * ```
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
abstract class EnumSeeder extends Seeder
{
    use ChecksOutdated;
    use ControlsWorkflow;

    /**
     * Run the enum database table seeding.
     *
     * @return void
     */
    public function run(): void
    {
        $db = $this->getConnection();
        $tableName = $this->table();
        $keyName = $this->keyName();

        $existingRecords = $db->table($tableName)
            ->get()
            ->keyBy('id');

        $insertData = [];
        $definedKeys = [];
        foreach ($this->rows() as $index => $row) {
            if (!isset($row[$keyName])) {
                throw new \LogicException("Missing key ('{$keyName}') field at row #{$index}");
            }

            $rowKey = $row[$keyName];

            if (in_array($rowKey, $definedKeys)) {
                throw new \LogicException("Key '{$rowKey}' defined at row #{$index} has been already defined earlier.");
            }

            if (isset($existingRecords[$row[$keyName]])) {
                $existingRecord = $existingRecords[$row[$keyName]];
                $definedKeys[] = $rowKey;
                unset($row[$keyName]);

                $attributes = $this->shouldUpdateExistingOnly();
                if (!empty($attributes)) {
                    if (!$this->isOutdated($existingRecord, $row, $attributes)) {
                        continue;
                    }

                    $updateAttributes = $this->shouldUpdateExistingWith();
                    foreach ($attributes as $attribute) {
                        $updateAttributes[$attribute] = $row[$attribute];
                    }

                    $db->table($tableName)
                        ->where($keyName, $rowKey)
                        ->update($updateAttributes);

                    continue;
                }

                $updateAttributes = $this->shouldUpdateExistingWith();
                if (!empty($updateAttributes) || $this->shouldUpdateExisting()) {
                    if (!$this->isOutdated($existingRecord, $row)) {
                        continue;
                    }

                    $db->table($tableName)
                        ->where($keyName, $rowKey)
                        ->update(array_merge($updateAttributes, $row));

                    continue;
                }

                continue;
            }

            $insertData[] = array_merge($this->shouldCreateWith(), $row);
            $definedKeys[] = $rowKey;
        }

        $db->table($tableName)->insert($insertData);

        $obsoleteKeys = array_diff($existingRecords->keys()->toArray(), $definedKeys);
        if (empty($obsoleteKeys)) {
            return;
        }

        if ($this->shouldDeleteObsolete()) {
            $db->table($tableName)
                ->whereIn($keyName, $obsoleteKeys)
                ->delete();

            return;
        }

        if ($attributes = $this->shouldUpdateObsoleteWith()) {
            $db->table($tableName)
                ->whereIn($keyName, $obsoleteKeys)
                ->update($attributes);
        }
    }

    /**
     * @return string name of the database table to be seeded.
     */
    abstract protected function table(): string;

    /**
     * Returns the database connection to be used for seeding.
     *
     * @return \Illuminate\Database\ConnectionInterface database connection instance.
     */
    protected function getConnection(): ConnectionInterface
    {
        return $this->getConnectionResolver()->connection($this->connectionName());
    }

    /**
     * Returns the database connection resolver.
     *
     * @return \Illuminate\Database\ConnectionResolverInterface database connection resolver.
     */
    protected function getConnectionResolver(): ConnectionResolverInterface
    {
        return $this->container->make('db');
    }

    /**
     * Defines name of the database connection to be used for seeding.
     * Value `null` means default connection.
     *
     * @return string|null database connection name.
     */
    protected function connectionName(): ?string
    {
        return null;
    }

    /**
     * Defines name of the column (attribute), which should be used to track records uniqueness (e.g. primary key).
     *
     * @return string unique key attribute name.
     */
    protected function keyName(): string
    {
        return 'id';
    }
}