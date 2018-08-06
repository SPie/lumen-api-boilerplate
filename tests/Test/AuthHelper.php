<?php

namespace Test;

use App\Models\Auth\LoginRefreshTokenDoctrineModel;
use App\Models\Auth\LoginRefreshTokenModelInterface;
use App\Repositories\Auth\LoginRefreshTokenRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * Trait AuthHelper
 *
 * @package Test
 */
trait AuthHelper
{

    /**
     * @param int   $times
     * @param array $data
     *
     * @return LoginRefreshTokenModelInterface[]|Collection
     */
    protected function createLoginRefreshTokens(int $times = 1, array $data = []): Collection
    {
        return $this->createModels(LoginRefreshTokenDoctrineModel::class, $times, $data);
    }

    /**
     * @return LoginRefreshTokenRepositoryInterface
     */
    protected function getLoginRefreshTokenRepository(): LoginRefreshTokenRepositoryInterface
    {
        return $this->app->get(LoginRefreshTokenRepositoryInterface::class);
    }
}