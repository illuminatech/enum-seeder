<?php

namespace Illuminatech\EnumSeeder;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Seeder;

abstract class EnumSeeder extends Seeder
{
    use WorkflowControl;

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
                throw new \LogicException("Missing key ({$keyName}) field at row #{$index}");
            }

            $rowKey = $row[$keyName];

            if (in_array($rowKey, $definedKeys)) {
                throw new \LogicException("Key '{$rowKey}' defined at row #{$index} has been already defined earlier.");
            }

            if (isset($existingRecords[$row[$keyName]])) {
                $definedKeys[] = $rowKey;

                if (!$this->shouldUpdateExisting()) {
                    continue;
                }

                $attributes = $this->shouldUpdateExistingOnlyWith();
                if (empty($attributes)) {
                    unset($row[$keyName]);

                    $db->table($tableName)
                        ->where($keyName, $rowKey)
                        ->update($row);

                    continue;
                }

                // @todo update only
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

    protected function getConnection(): ConnectionInterface
    {
        return $this->getConnectionResolver()->connection($this->connectionName());
    }

    protected function getConnectionResolver(): ConnectionResolverInterface
    {
        return $this->container->make('db');
    }

    protected function connectionName(): ?string
    {
        return null;
    }

    protected function keyName(): ?string
    {
        return 'id';
    }
}