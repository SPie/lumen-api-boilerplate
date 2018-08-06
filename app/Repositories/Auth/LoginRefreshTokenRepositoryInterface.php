<?php

namespace App\Repositories\Auth;

use App\Models\Auth\LoginRefreshTokenModelInterface;
use App\Models\ModelInterface;
use App\Repositories\RepositoryInterface;

/**
 * Interface LoginRefreshTokenRepositoryInterface
 *
 * @package App\Repositories\Auth
 */
interface LoginRefreshTokenRepositoryInterface extends RepositoryInterface
{

    /**
     * @param LoginRefreshTokenModelInterface|ModelInterface $model
     * @param bool                                           $flush
     *
     * @return LoginRefreshTokenModelInterface|ModelInterface
     */
    public function save(ModelInterface $model, bool $flush = true): ModelInterface;

    /**
     * @param string $token
     *
     * @return LoginRefreshTokenModelInterface|null
     */
    public function findOneByToken(string $token): ?LoginRefreshTokenModelInterface;
}