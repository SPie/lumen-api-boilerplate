<?php

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use LaravelDoctrine\Migrations\Schema\Builder;
use LaravelDoctrine\Migrations\Schema\Table;

/**
 * Class Version00000000000000
 *
 * @package Database\Migrations
 */
class Version00000000000000 extends AbstractMigration
{

    //region Up calls

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this
            ->createUsersTable($schema);
    }

    /**
     * @param Schema $schema
     *
     * @return $this
     */
    private function createUsersTable(Schema $schema)
    {
        (new Builder($schema))->create('users', function (Table $table) {
            $table->increments('id');
            $table->string('email');
            $table->unique('email');
            $table->string('password');
            $table->timestamps();
            $table->softDeletes();
        });

        return $this;
    }

    //endregion

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        //no rollback
    }
}