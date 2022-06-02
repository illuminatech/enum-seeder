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

Almost every project requires specification of so called 'dictionary' or 'enum' entities, such as statuses, types,
categories and so on. It is not always practical to keep such data as [PHP enums](https://www.php.net/manual/en/language.enumerations.php)
or [class-base enums](https://github.com/myclabs/php-enum). Sometimes it has to be put into a database table. For example:
when we need to provide ability for the system administrator to edit human-readable title or description of the particular
category or status, or enable/disable particular records, or simply to keep the database integrity.

Obviously keeping dictionary (enum) in the database tables creates a problem of its synchronization. As our project evolves
new categories and statuses may appear, and some may become obsolete. Thus, we need a tool, which allows updating of the
data in the dictionary (enum) tables. This package provides such a tool.

The idea is in creation of the special kind of database seeder, which synchronizes particular enum
table with the predefined data in the way, it could be invoked multiple times without creation of redundant records or
breaking an integrity. You can create such seeder extending `Illuminatech\EnumSeeder\EnumSeeder`. For example:

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
        $this->call(ContentPageSeeder::class);
        // ...
    }
}
```

With seeders defined in such way, you can invoke following command after each project update:

```
php artisan migrate --seed
```

As the result table 'item_categories' will always be up-to-date with the values from `ItemCategorySeeder::rows()`.
In case you need to add a new item category, you can simply add another entry to the `ItemCategorySeeder::rows()` and
run the seeder again. It will gracefully add the missing records, keeping already existing ones intact.

You can control the seeding options overriding methods from [Illuminatech\EnumSeeder\ControlsWorkflow](src/ControlsWorkflow.php).

**Heads up!** Make sure you do not setup a sequence (autoincrement) for the primary key (id) of the dictionary (enum) table,
otherwise `EnumSeeder` may be unable to properly handle its data synchronization.

The example of the database migration for the dictionary (enum) table:

```php
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemStatusTable extends Migration
{
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->unsignedSmallInteger('id')->primary(); // no sequence (autoincrement)
            $table->string('name');
            // ...
        });
    }
}
```

> Tip: Remember that it is not mandatory for primary key field to be always and integer - you may use strings for it just
  as well, keeping your database records more human-readable. For example:

```php
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemStatusTable extends Migration
{
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->string('name');
            // ...
        });
    }
}
```


### Processing of the obsolete records

By default `Illuminatech\EnumSeeder\EnumSeeder` does not deletes the records, which are no longer specified at `rows()`
method, as the database may already contain the references to those records via foreign keys, and deleting enum value
may cause a data loss. However, if you sure what you are doing, you can control the deletion feature via
`shouldDeleteObsolete()` method. For example:

```php
<?php

use Illuminatech\EnumSeeder\EnumSeeder;

class ContentPageSeeder extends EnumSeeder
{
    protected function table(): string
    {
        return 'content_pages';
    }
    
    protected function rows() : array
    {
        return [
            [
                'id' => 1,
                'name' => 'About Us',
                'slug' => 'about-us',
                'content' => '<div>...</div>',
            ],
            [
                'id' => 2,
                'name' => 'How it works',
                'slug' => 'how-it-works',
                'content' => '<div>...</div>',
            ],
            // ...
        ];
    }
    
    protected function shouldDeleteObsolete(): bool
    {
        return true; // always delete records from 'content_pages', which 'id' is missing at `rows()`
    }
}
```

Each time `ContentPageSeeder` will be called, it will delete all the records from 'content_pages', which 'id' is missing
at `rows()` method declaration.

> Note: Remember that you can specify a complex logic at `shouldDeleteObsolete()` to suite your needs. For example, you
  can allow deleting of obsolete rows for "local" environment, while forbidding it for "prod":

```php
<?php

use Illuminatech\EnumSeeder\EnumSeeder;

class ItemStatusSeeder extends EnumSeeder
{
    // ...
    
    protected function shouldDeleteObsolete(): bool
    {
        return $this->container->environment('local'); // allows deletion of the records only in "local" environment
    }
}
```

Deletion is not the only way to deal with obsolete records. In order to keep the database integrity, it is better to
simply mark the obsolete records as outdated, e.g. perform a "soft-delete". This can be achieved via `shouldUpdateObsoleteWith()`
method. For example:

```php
<?php

use Illuminatech\EnumSeeder\EnumSeeder;

class ItemStatusSeeder extends EnumSeeder
{
    // ...
    
    protected function shouldUpdateObsoleteWith(): array
    {
        // following attributes will be applied to the records, which 'id' is missing at `rows()`
        return [
            'deleted_at' => now(),
        ];
    }
}
```


### Common data for the records creation

You may simplify `rows()` method for the particular seeder extracting common attributes in `shouldCreateWith()` method.
It defines the default attribute values for each created record, unless it explicitly overridden by the entry from `rows()`.
For example:

```php
<?php

use Illuminatech\EnumSeeder\EnumSeeder;

class ItemCategorySeeder extends EnumSeeder
{
    protected function shouldCreateWith(): array
    {
        // applies following attributes per each new created record:
        return [
            'is_active' => true,
            'created_at' => now(),
        ];
    }
    
    protected function rows() : array
    {
        return [
            [
                'id' => 1,
                'name' => 'Active Category',
            ],
            [
                'id' => 2,
                'name' => 'Inactive Category',
                'is_active' => false, // overrides the value from `shouldCreateWith()`
            ],
            // ...
        ];
    }
    
    // ...
}
```


### Updating of the existing records


### Eloquent enum seeders

It might be more convenient for you to operate Eloquent models instead of plain tables for enum seeding. Manipulating data
via active record models allows you to use its full features such as "events", "timestamps" and "soft-delete".
You can setup a enum seeder for particular Eloquent model using `Illuminatech\EnumSeeder\EloquentEnumSeeder`. 
For example:

```php
<?php

use App\Models\ItemCategory;
use Illuminatech\EnumSeeder\EloquentEnumSeeder;

class ItemCategorySeeder extends EloquentEnumSeeder
{
    protected function model(): string
    {
        return ItemCategory::class;
    }
    
    protected function rows(): array
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

**Heads up!** Remember to disable `Illuminate\Database\Eloquent\Model::$incrementing` for the enum Eloquent model,
otherwise `EloquentEnumSeeder` may be unable to properly handle its data synchronization. For example:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemCategory extends Model
{
    /**
     * {@inheritdoc}
     */
    public $incrementing = false; // disable auto-increment

    // ... 
}
```

**Heads up!** In case you are using string values as a primary key for enum table, you should also adjust
`Illuminate\Database\Eloquent\Model::$keyType` accordingly. For example:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemCategory extends Model
{
    /**
     * {@inheritdoc}
     */
    public $incrementing = false; // disable auto-increment
    
    /**
     * {@inheritdoc}
     */
    protected $keyType = 'string';  // setup 'string' type for primary key

    // ... 
}
```
