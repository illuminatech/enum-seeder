<?php

namespace Illuminatech\EnumSeeder\Test;

use Illuminatech\EnumSeeder\EnumSeeder;

class EnumSeederTest extends TestCase
{
    public function testCreateRows()
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

    /**
     * @param string $table
     * @param array $rows
     * @return \Illuminatech\EnumSeeder\EnumSeeder|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function mockEnum(string $table, array $rows)
    {
        $seeder = $this->getMockBuilder(EnumSeeder::class)
            ->getMockForAbstractClass();

        $seeder->method('table')->willReturn($table);
        $seeder->method('rows')->willReturn($rows);

        return $seeder;
    }
}