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
     * @param JWTObject|null $jwtObject
     *
     * @return $this
     */
    public function setJWTObject(?JWTObject $jwtObject);

    /**
     * @return JWTObject|null
     */
    public function getJWTObject(): ?JWTObject;
}