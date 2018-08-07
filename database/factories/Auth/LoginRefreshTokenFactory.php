<?php

use App\Models\Auth\LoginRefreshTokenDoctrineModel;
use App\Models\Auth\LoginRefreshTokenModelInterface;
use App\Models\User\UserDoctrineModel;
use Faker\Generator as Faker;
use LaravelDoctrine\ORM\Testing\Factory;

/**
 * @var Factory $factory
 */


$factory->define(LoginRefreshTokenDoctrineModel::class, function (Faker $faker) {
    return [
        LoginRefreshTokenModelInterface::PROPERTY_TOKEN       => $faker->uuid,
        LoginRefreshTokenModelInterface::PROPERTY_DISABLED_AT => $faker->dateTime,
        LoginRefreshTokenModelInterface::PROPERTY_USER        => entity(UserDoctrineModel::class, 1)->create(),
    ];
});