<?php

namespace Hichxm\LaravelPhones\Test;

use Hichxm\LaravelPhones\LaravelPhonesServiceProvider;
use Hichxm\LaravelPhones\Test\Models\Person;
use Hichxm\LaravelPhones\Test\Models\PersonWithSoftDelete;
use Hichxm\LaravelSortable\LaravelSortableServiceProvider;
use Illuminate\Database\Schema\Blueprint;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{

    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function setUpDatabase(): void
    {
        $this->app['db']->connection()->getSchemaBuilder()->create('persons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        $migration = include __DIR__.'/../database/migrations/create_phones_table.php.stub';
        $migration->up();
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelSortableServiceProvider::class,
            LaravelPhonesServiceProvider::class,
        ];
    }

    /**
     * Create a dummy person
     *
     * @param array $attributes
     * @param string $model
     * @return Person|PersonWithSoftDelete
     */
    protected function createDummyPerson(array $attributes = [], string $model = Person::class): Person|PersonWithSoftDelete
    {
        /** @var Person $person */
        $person = $this->app->make($model)::query()
            ->create(array_merge([
                'name' => 'John Doe',
            ], $attributes));

        return $person;
    }

    /**
     * @param int $count
     * @return Person[]
     */
    protected function createDummiesPersons(int $count = 10): array
    {
        $persons = [];

        for ($i = 0; $i < $count; $i++) {
            $persons[] = $this->createDummyPerson([
                'name' => 'Person ' . $i,
            ]);
        }

        return $persons;
    }

}