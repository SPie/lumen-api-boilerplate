<?php

namespace App\Models\User;

use App\Models\Auth\LoginRefreshTokenModelInterface;
use App\Models\ModelInterface;
use App\Models\SoftDeletable;
use App\Models\Timestampable;
use App\Services\JWT\JWTAuthenticatable;
use Illuminate\Support\Collection;

/**
 * Interface UserModelInterface
 *
 * @package App\Models\User
 */
interface UserModelInterface extends ModelInterface, Timestampable, SoftDeletable, JWTAuthenticatable
{

    const PROPERTY_EMAIL    = 'email';
    const PROPERTY_PASSWORD = 'password';
    const PROPERTY_LOGIN_REFRESH_TOKENS = 'loginRefreshTokens';

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setEmail(string $email);

    /**
     * @return string
     */
    public function getEmail(): string;

    /**
     * @param string $password
     *
     * @return $this
     */
    public function setPassword(string $password);

    /**
     * @param LoginRefreshTokenModelInterface[] $loginRefreshTokens
     *
     * @return UserModelInterface
     */
    public function setLoginRefreshTokens(array $loginRefreshTokens): UserModelInterface;

    /**
     * @param LoginRefreshTokenModelInterface $loginRefreshToken
     *
     * @return UserModelInterface
     */
    public function addLoginRefreshToken(LoginRefreshTokenModelInterface $loginRefreshToken): UserModelInterface;

    /**
     * @return LoginRefreshTokenModelInterface[]|Collection
     */
    public function getLoginRefreshTokens(): Collection;
}