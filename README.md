<p align="center">
    <a href="https://github.com/illuminatech" target="_blank">
        <img src="https://avatars1.githubusercontent.com/u/47185924" height="100px">
    </a>
    <h1 align="center">Laravel Enum Seeder</h1>
    <br>
</p>

This extension allows easy creation of DB seeders for the dictionary (enum) type tables, such as statuses, types,
categories and so on.

For license information check the [LICENSE](LICENSE.md)-file.

[![Latest Stable Version](https://img.shields.io/packagist/v/illuminatech/enum-seeder.svg)](https://packagist.org/packages/illuminatech/enum-seeder)
[![Total Downloads](https://img.shields.io/packagist/dt/illuminatech/enum-seeder.svg)](https://packagist.org/packages/illuminatech/enum-seeder)
[![Build Status](https://github.com/illuminatech/enum-seeder/workflows/build/badge.svg)](https://github.com/illuminatech/enum-seeder/actions)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist illuminatech/enum-seeder
```

or add

```json
"illuminatech/enum-seeder": "*"
```

to the "require" section of your composer.json.


Usage
-----

This extension allows easy creation of DB seeders for the dictionary (enum) type tables, such as statuses, types,
categories and so on.

For example:

```php
<?php

namespace Database\Seeders;

use Illuminatech\EnumSeeder\EnumSeeder;

class ItemCategorySeeder extends EnumSeeder
{
    protected function table(): string
    {
        return 'item_categories';
    }
    
    protected function rows() : array
    {
        return [
            [
                'id' => 1,
                'name' => 'Consumer goods',
                'slug' => 'consumer-goods',
            ],
            [
                'id' => 2,
                'name' => 'Health care',
                'slug' => 'health-care',
            ],
            // ...
        ];
    }
}

// ...

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // always synchronize all dictionary (enum) tables:
        $this->call(ItemCategorySeeder::class);
        $this->call(ItemStatusSeeder::class);
        // ...
        
        if (app()->environment('local')) {
            // seed demo data for local environment only
            $this->call(DemoSeeder::class);
        }
    }
}
```

Eloquent example:

```php
<?php

use App\Models\ItemCategory;
use Illuminatech\EnumSeeder\EloquentEnumSeeder;

class ItemCategorySeeder extends EloquentEnumSeeder
{
    protected function model() : string
    {
        return ItemCategory::class;
    }
    
    protected function rows() : array
    {
        return [
            [
                'id' => 1,
                'name' => 'Consumer goods',
                'slug' => 'consumer-goods',
            ],
            [
                'id' => 2,
                'name' => 'Health care',
                'slug' => 'health-care',
            ],
            // ...
        ];
    }
}
```
