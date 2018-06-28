<?php

namespace Test;

/**
 * Trait DatabaseMigrations
 *
 * @package Test
 */
trait DatabaseMigrations
{
    /**
     * Define hooks to migrate the database before and after each test.
     *
     * @return void
     */
    public function runDatabaseMigrations()
    {
        $this->artisan('doctrine:migrations:refresh');

        $this->beforeApplicationDestroyed(function () {
            $this->artisan('doctrine:migrations:reset');
        });
    }
}