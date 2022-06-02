<?php

namespace Illuminatech\EnumSeeder;

use Illuminate\Database\Seeder;

/**
 * EloquentEnumSeeder allows synchronization for the dictionary (enum) data per single Eloquent model.
 *
 * Usage example:
 *
 * ```
 * use App\Models\ItemStatus;
 * use Illuminatech\EnumSeeder\EloquentEnumSeeder;
 *
 * class ItemStatusSeeder extends EloquentEnumSeeder
 * {
 *     protected function model() : string
 *     {
 *         return ItemStatus::class;
 *     }
 *
 *     protected function rows() : array
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
abstract class EloquentEnumSeeder extends Seeder
{
    use ChecksOutdated;
    use ControlsWorkflow;

    /**
     * Defines the name of the Eloquent model class, which shuld be seeded.
     *
     * @return string Eloquent model class name.
     */
    abstract protected function model(): string;

    /**
     * Run the enum database model seeding.
     *
     * @return void
     */
    public function run(): void
    {
        /** @var \Illuminate\Database\Eloquent\Model|string $modelClass */
        $modelClass = $this->model();
        $keyName = $modelClass::query()->getModel()->getKeyName();

        /** @var \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model[] $existingModels */
        $existingModels = $modelClass::query()
            ->withoutGlobalScopes()
            ->get()
            ->keyBy($keyName);

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

            if (isset($existingModels[$row[$keyName]])) {
                $definedKeys[] = $rowKey;

                $existingModel = $existingModels[$row[$keyName]];
                unset($row[$keyName]);

                $attributes = $this->shouldUpdateExistingOnly();
                if (!empty($attributes)) {
                    if (!$this->isOutdated($existingModel, $row, $attributes)) {
                        continue;
                    }

                    $updateAttributes = $this->shouldUpdateExistingWith();
                    foreach ($attributes as $attribute) {
                        $updateAttributes[$attribute] = $row[$attribute];
                    }

                    $existingModel->forceFill($updateAttributes);
                    $existingModel->save();

                    continue;
                }

                $updateAttributes = $this->shouldUpdateExistingWith();
                if (!empty($updateAttributes) || $this->shouldUpdateExisting()) {
                    if (!$this->isOutdated($existingModel, $row)) {
                        continue;
                    }

                    $existingModel->forceFill(array_merge($row, $updateAttributes));
                    $existingModel->save();

                    continue;
                }

                continue;
            }

            $insertData[] = array_merge($this->shouldCreateWith(), $row);
            $definedKeys[] = $rowKey;
        }

        foreach ($insertData as $insertAttributes) {
            /** @var \Illuminate\Database\Eloquent\Model $model */
            $model = new $modelClass();
            $model->forceFill($insertAttributes);
            $model->save();
        }

        $obsoleteKeys = array_diff($existingModels->keys()->toArray(), $definedKeys);
        if (empty($obsoleteKeys)) {
            return;
        }

        if ($this->shouldDeleteObsolete()) {
            foreach ($obsoleteKeys as $obsoleteKey) {
                $existingModels[$obsoleteKey]->delete();
            }

            return;
        }

        if ($attributes = $this->shouldUpdateObsoleteWith()) {
            foreach ($obsoleteKeys as $obsoleteKey) {
                $model = $existingModels[$obsoleteKey];
                $model->forceFill($attributes);
                $model->save();
            }
        }
    }
}