<?php

use App\Models\User\UserDoctrineModel;
use Illuminate\Database\Seeder;

/**
 * Class UsersSeeder
 */
class UsersSeeder extends Seeder
{

    /**
     * @return void
     */
    public function run(): void
    {
        entity(UserDoctrineModel::class, 5)->create();
    }
}