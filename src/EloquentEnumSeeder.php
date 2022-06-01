<?php

namespace Illuminatech\EnumSeeder;

use Illuminate\Database\Seeder;

abstract class EloquentEnumSeeder extends Seeder
{
    use WorkflowControl;

    abstract protected function model(): string;
}