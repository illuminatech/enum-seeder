<?php

namespace Illuminatech\EnumSeeder\Test;

use Illuminate\Container\Container;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Facade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager;

/**
 * Base class for the test cases.
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Illuminate\Contracts\Container\Container test application instance.
     */
    protected $app;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createApplication();

        $db = new Manager;

        $db->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);

        $db->bootEloquent();
        $db->setAsGlobal();

        $this->app->instance('db', $db->getDatabaseManager());

        Model::clearBootedModels();

        $this->createSchema();
    }

    /**
     * Get a database connection instance.
     *
     * @return \Illuminate\Database\Connection
     */
    protected function getConnection()
    {
        return Model::getConnectionResolver()->connection();
    }

    /**
     * Get a schema builder instance.
     *
     * @return \Illuminate\Database\Schema\Builder
     */
    protected function getSchemaBuilder()
    {
        return $this->getConnection()->getSchemaBuilder();
    }

    /**
     * Creates dummy application instance, ensuring facades functioning.
     */
    protected function createApplication()
    {
        $this->app = new Container();

        Container::setInstance($this->app);

        Facade::setFacadeApplication($this->app);
    }

    /**
     * Setup the database schema.
     *
     * @return void
     */
    protected function createSchema(): void
    {
        $this->getSchemaBuilder()->create('statuses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
        });

        $this->getSchemaBuilder()->create('categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('type_id');
            $table->string('name');
            $table->string('slug');
        });
    }

    /**
     * Applies given DB seeder.
     *
     * @param \Illuminate\Database\Seeder $seeder
     */
    protected function callSeeder(Seeder $seeder): void
    {
        $seeder->setContainer($this->app);
        $this->app->call([$seeder, 'run']);
    }
}
