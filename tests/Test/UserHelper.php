<?php

namespace Test;

use App\Models\User\UserDoctrineModel;
use App\Models\User\UserModelFactoryInterface;
use App\Models\User\UserModelInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\User\UsersServiceInterface;
use Illuminate\Support\Collection;

/**
 * Trait UserHelper
 *
 * @package Test
 */
trait UserHelper
{

    /**
     * @param int    $times
     * @param array  $data
     *
     * @return UserModelInterface[]|Collection
     */
    protected function createUsers(int $times = 1, array $data = []): Collection
    {
        return $this->createModels(UserDoctrineModel::class, $times, $data);
    }

    /**
     * @param string      $modelClass
     * @param int         $times
     * @param array       $data
     * @param string|null $state
     *
     * @return Collection
     */
    private function createModels(
        string $modelClass,
        int $times = 1,
        array $data = [],
        string $state = null
    ): Collection
    {
        if ($times == 1) {
            return new Collection([
                $state
                    ? entity($modelClass, $state, $times)->create($data)
                    : entity($modelClass, $times)->create($data)
            ]);
        }

        return $state
            ? entity($modelClass, $state, $times)->create($data)
            : entity($modelClass, $times)->create($data);
    }

    /**
     * @return UserRepositoryInterface
     */
    protected function getUserRepository(): UserRepositoryInterface
    {
        return $this->app->get(UserRepositoryInterface::class);
    }

    /**
     * @return UsersServiceInterface
     */
    protected function getUserService(): UsersServiceInterface
    {
        return $this->app->get(UsersServiceInterface::class);
    }

    /**
     * @return UserModelFactoryInterface
     */
    protected function getUserModelFactory(): UserModelFactoryInterface
    {
        return $this->app->get(UserModelFactoryInterface::class);
    }
}