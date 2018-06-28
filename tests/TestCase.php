<?php

use Faker\Factory;
use Faker\Generator;
use Laravel\Lumen\Testing\DatabaseMigrations as EloquentDatabaseMigrations;
use Test\DatabaseMigrations;

/**
 * Class TestCase
 */
abstract class TestCase extends Laravel\Lumen\Testing\TestCase
{
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    /**
     * @return void
     */
    public function setUpTraits()
    {
        parent::setUpTraits();

        $uses = array_flip(class_uses_recursive(get_class($this)));

        if (isset($uses[DatabaseMigrations::class]) && !isset($uses[EloquentDatabaseMigrations::class])) {
            $this->runDatabaseMigrations();
        }
    }

    /**
     * @return Generator
     */
    protected function getFaker(): Generator
    {
        if (!isset($this->faker)) {
            $this->faker = Factory::create();
        }

        return $this->faker;
    }
}
