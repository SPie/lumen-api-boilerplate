<?php

namespace App\Models\Auth;

use App\Models\ModelFactoryInterface;
use App\Models\ModelInterface;
use App\Models\User\UserModelFactoryInterface;

/**
 * Interface LoginRefreshTokenModelFactoryInterface
 *
 * @package App\Models\Auth
 */
interface LoginRefreshTokenModelFactoryInterface extends ModelFactoryInterface
{

    /**
     * @param UserModelFactoryInterface $userModelFactory
     *
     * @return LoginRefreshTokenModelFactoryInterface
     */
    public function setUserModelFactory(
        UserModelFactoryInterface $userModelFactory
    ): LoginRefreshTokenModelFactoryInterface;

    /**
     * @param array $data
     *
     * @return LoginRefreshTokenModelInterface|ModelInterface
     */
    public function create(array $data): ModelInterface;

    /**
     * @param LoginRefreshTokenModelInterface|ModelInterface $model
     * @param array                                          $data
     *
     * @return LoginRefreshTokenModelInterface|ModelInterface
     */
    public function fill(ModelInterface $model, array $data): ModelInterface;
}