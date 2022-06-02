<?php

namespace Illuminatech\EnumSeeder\Test;

use Illuminatech\EnumSeeder\EloquentEnumSeeder;
use Illuminatech\EnumSeeder\Test\Support\Category;
use Illuminatech\EnumSeeder\Test\Support\Status;

class EloquentEnumSeederTest extends AbstractEnumSeederTest
{
   /**
     * {@inheritdoc}
     */
    protected function mockEnum(string $table, array $rows)
    {
        $modelClasses = [
            Status::class,
            Category::class,
        ];

        $model = null;
        foreach ($modelClasses as $modelClass) {
            /** @var \Illuminate\Database\Eloquent\Model|string $modelClass */
            if ($modelClass::query()->getModel()->getTable() === $table) {
                $model = $modelClass;
                break;
            }
        }

        if (empty($model)) {
            throw new \LogicException("Unable to locate model for table '{$table}'");
        }

        $seeder = $this->getMockBuilder(EloquentEnumSeeder::class)
            ->onlyMethods([
                'model',
                'rows',
                'shouldDeleteObsolete',
                'shouldUpdateObsoleteWith',
                'shouldCreateWith',
                'shouldUpdateExisting',
                'shouldUpdateExistingOnly',
                'shouldUpdateExistingWith',
            ])
            ->getMock();

        $seeder->method('model')->willReturn($model);
        $seeder->method('rows')->willReturn($rows);

        return $seeder;
    }
}