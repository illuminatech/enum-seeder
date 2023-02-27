<?php

namespace Illuminatech\EnumSeeder\Test;

use Illuminatech\EnumSeeder\EnumSeeder;

class EnumSeederTest extends AbstractEnumSeederTestCase
{
    public function testUpdateExistingCustomKeyName()
    {
        $rows = [
            [
                'type_id' => 5,
                'name' => 'updated name',
                'slug' => 'updated slug',
            ],
        ];
        $seeder = $this->mockEnum('categories', $rows, 'type_id');

        $this->getConnection()->table('categories')
            ->insert([
                [
                    'type_id' => 5,
                    'name' => 'outdated name',
                    'slug' => 'outdated slug',
                ],
            ]);

        $seeder->method('shouldUpdateExisting')->willReturn(true);
        $this->callSeeder($seeder);

        $rows = $this->getConnection()->table('categories')->get()->toArray();
        $this->assertCount(1, $rows);
        $this->assertEquals('updated slug', $rows[0]->slug);
    }

    /**
     * {@inheritdoc}
     */
    protected function mockEnum(string $table, array $rows, string $keyName = 'id')
    {
        $seeder = $this->getMockBuilder(EnumSeeder::class)
            ->onlyMethods([
                'table',
                'rows',
                'keyName',
                'shouldDeleteObsolete',
                'shouldUpdateObsoleteWith',
                'shouldCreateWith',
                'shouldUpdateExisting',
                'shouldUpdateExistingOnly',
                'shouldUpdateExistingWith',
            ])
            ->getMock();

        $seeder->method('table')->willReturn($table);
        $seeder->method('rows')->willReturn($rows);
        $seeder->method('keyName')->willReturn($keyName);

        return $seeder;
    }
}