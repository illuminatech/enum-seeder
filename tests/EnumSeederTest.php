<?php

namespace Illuminatech\EnumSeeder\Test;

use Illuminatech\EnumSeeder\EnumSeeder;

class EnumSeederTest extends AbstractEnumSeederTest
{
    /**
     * {@inheritdoc}
     */
    protected function mockEnum(string $table, array $rows)
    {
        $seeder = $this->getMockBuilder(EnumSeeder::class)
            ->onlyMethods([
                'table',
                'rows',
                'shouldDeleteObsolete',
                'shouldUpdateObsoleteWith',
                'shouldCreateWith',
                'shouldUpdateExisting',
                'shouldUpdateExistingOnlyWith',
            ])
            ->getMock();

        $seeder->method('table')->willReturn($table);
        $seeder->method('rows')->willReturn($rows);

        return $seeder;
    }
}