<?php

namespace Illuminatech\EnumSeeder\Test;

abstract class AbstractEnumSeederTest extends TestCase
{
    public function testCreateMissing()
    {
        $seeder = $this->mockEnum('statuses', [
            [
                'id' => 11,
                'name' => 'first',
            ],
            [
                'id' => 22,
                'name' => 'second',
            ],
        ]);

        $this->callSeeder($seeder);

        $rows = $this->getConnection()
            ->table('statuses')
            ->get()
            ->toArray();

        $this->assertCount(2, $rows);
        $this->assertEquals(11, $rows[0]->id);
        $this->assertEquals(22, $rows[1]->id);
        $this->assertSame('first', $rows[0]->name);
        $this->assertSame('second', $rows[1]->name);
    }

    public function testUpdateExisting()
    {
        $rows = [
            [
                'id' => 11,
                'name' => 'updated',
            ],
        ];
        $seeder = $this->mockEnum('statuses', $rows);

        $this->getConnection()->table('statuses')
            ->insert([
                [
                    'id' => 11,
                    'name' => 'outdated',
                ],
            ]);

        $seeder->method('shouldUpdateExisting')->willReturn(true);
        $this->callSeeder($seeder);

        $rows = $this->getConnection()->table('statuses')->get()->toArray();
        $this->assertCount(1, $rows);
        $this->assertEquals('updated', $rows[0]->name);
    }

    public function testSkipExisting()
    {
        $seeder = $this->mockEnum('statuses', [
            [
                'id' => 11,
                'name' => 'first',
            ],
            [
                'id' => 22,
                'name' => 'second',
            ],
        ]);

        $this->getConnection()->table('statuses')
            ->insert([
                [
                    'id' => 11,
                    'name' => 'first',
                ],
            ]);

        $seeder->method('shouldUpdateExisting')->willReturn(false);
        $this->callSeeder($seeder);

        $rows = $this->getConnection()
            ->table('statuses')
            ->get()
            ->toArray();

        $this->assertCount(2, $rows);
        $this->assertEquals(11, $rows[0]->id);
        $this->assertEquals(22, $rows[1]->id);
        $this->assertSame('first', $rows[0]->name);
        $this->assertSame('second', $rows[1]->name);
    }

    public function testPreserveObsolete()
    {
        $rows = [
            [
                'id' => 11,
                'name' => 'first',
            ],
        ];
        $seeder = $this->mockEnum('statuses', $rows);

        $this->getConnection()->table('statuses')
            ->insert(array_merge($rows, [
                [
                    'id' => 22,
                    'name' => 'second',
                ],
            ]));

        $seeder->method('shouldDeleteObsolete')->willReturn(false);
        $this->callSeeder($seeder);

        $this->assertSame(2, $this->getConnection()->table('statuses')->count());
    }

    public function testDeleteObsolete()
    {
        $rows = [
            [
                'id' => 11,
                'name' => 'first',
            ],
        ];
        $seeder = $this->mockEnum('statuses', $rows);

        $this->getConnection()->table('statuses')
            ->insert(array_merge($rows, [
                [
                    'id' => 22,
                    'name' => 'second',
                ],
            ]));

        $seeder->method('shouldDeleteObsolete')->willReturn(true);
        $this->callSeeder($seeder);

        $rows = $this->getConnection()->table('statuses')->get()->toArray();
        $this->assertCount(1, $rows);
        $this->assertEquals(11, $rows[0]->id);
    }

    public function testDeleteUpdateObsoleteWith()
    {
        $rows = [
            [
                'id' => 11,
                'name' => 'first',
            ],
        ];
        $seeder = $this->mockEnum('statuses', $rows);

        $this->getConnection()->table('statuses')
            ->insert(array_merge($rows, [
                [
                    'id' => 22,
                    'name' => 'second',
                ],
            ]));

        $seeder->method('shouldUpdateObsoleteWith')->willReturn([
            'name' => 'deleted',
        ]);
        $this->callSeeder($seeder);

        $rows = $this->getConnection()->table('statuses')->get()->toArray();
        $this->assertCount(2, $rows);
        $this->assertEquals('first', $rows[0]->name);
        $this->assertEquals('deleted', $rows[1]->name);
    }

    public function testCreateWith()
    {
        $rows = [
            [
                'id' => 11,
            ],
        ];
        $seeder = $this->mockEnum('statuses', $rows);

        $seeder->method('shouldCreateWith')->willReturn([
            'name' => 'created-with',
        ]);
        $this->callSeeder($seeder);

        $rows = $this->getConnection()->table('statuses')->get()->toArray();
        $this->assertCount(1, $rows);
        $this->assertEquals('created-with', $rows[0]->name);
    }

    public function testUpdateExistingOnly()
    {
        $rows = [
            [
                'id' => 11,
                'type_id' => 1,
                'name' => 'first name',
                'slug' => 'first-slug',
            ],
        ];
        $seeder = $this->mockEnum('categories', $rows);

        $this->getConnection()->table('categories')
            ->insert([
                [
                    'id' => 11,
                    'type_id' => 2,
                    'name' => 'outdated name',
                    'slug' => 'outdated-slug',
                ],
            ]);

        $seeder->method('shouldUpdateExistingOnly')->willReturn([
            'name',
            'slug',
        ]);
        $this->callSeeder($seeder);

        $rows = $this->getConnection()->table('categories')->get()->toArray();
        $this->assertCount(1, $rows);
        $this->assertEquals(2, $rows[0]->type_id);
        $this->assertEquals('first name', $rows[0]->name);
        $this->assertEquals('first-slug', $rows[0]->slug);
    }

    public function testUpdateExistingWith()
    {
        $rows = [
            [
                'id' => 11,
                'type_id' => 1,
                'slug' => 'first-slug',
            ],
        ];
        $seeder = $this->mockEnum('categories', $rows);

        $this->getConnection()->table('categories')
            ->insert([
                [
                    'id' => 11,
                    'type_id' => 2,
                    'name' => 'outdated name',
                    'slug' => 'outdated-slug',
                ],
            ]);

        $seeder->method('shouldUpdateExistingWith')->willReturn([
            'name' => 'overridden name',
        ]);
        $this->callSeeder($seeder);

        $rows = $this->getConnection()->table('categories')->get()->toArray();
        $this->assertCount(1, $rows);
        $this->assertEquals(1, $rows[0]->type_id);
        $this->assertEquals('overridden name', $rows[0]->name);
        $this->assertEquals('first-slug', $rows[0]->slug);
    }

    /**
     * @param string $table
     * @param array $rows
     * @return \Illuminate\Database\Seeder|\PHPUnit\Framework\MockObject\MockObject
     */
    abstract protected function mockEnum(string $table, array $rows);
}