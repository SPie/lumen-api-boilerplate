<?php

namespace App\Services\Auth;

use App\Models\Auth\LoginRefreshTokenModelInterface;
use App\Models\User\UserModelInterface;

/**
 * Interface LoginRefreshTokenServiceInterface
 *
 * @package App\Services\Auth
 */
interface LoginRefreshTokenServiceInterface
{

    /**
     * @param UserModelInterface $user
     *
     * @return LoginRefreshTokenModelInterface
     */
    public function createLoginRefreshToken(UserModelInterface $user): LoginRefreshTokenModelInterface;

    /**
     * @param string $token
     *
     * @return UserModelInterface|null
     */
    public function useLoginRefreshToken(string $token): ?UserModelInterface;

    /**
     * @param string $token
     *
     * @return LoginRefreshTokenServiceInterface
     */
    public function disableRefreshToken(string $token): LoginRefreshTokenServiceInterface;
}