<?php

namespace App\Services\JWT;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Interface JWTAuthenticatable
 *
 * @package App\Services\JWT
 */
interface JWTAuthenticatable extends Authenticatable
{

    /**
     * @return int
     */
    public function getJWTIdentifier(): int;

    /**
     * @return array
     */
    public function getCustomClaims(): array;

    /**
     * @param null|string $jwtRefreshToken
     *
     * @return $this
     */
    public function setUsedJWTRefreshToken(?string $jwtRefreshToken);

    /**
     * @return string|null
     */
    public function getUsedJWTRefreshToken(): ?string;
}