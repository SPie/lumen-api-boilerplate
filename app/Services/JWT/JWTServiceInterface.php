<?php

namespace App\Services\JWT;

use App\Services\User\UsersServiceInterface;

/**
 * Interface JWTServiceInterface
 *
 * @package App\Services\JWT
 */
interface JWTServiceInterface
{

    const AUTHORIZATION_BEARER = 'authorization';

    /**
     * @param JWTAuthenticatable $user
     *
     * @return JWTObject
     */
    public function createToken(JWTAuthenticatable $user): JWTObject;

    /**
     * @param JWTAuthenticatable $user
     *
     * @return JWTObject
     */
    public function refreshAuthToken(JWTAuthenticatable $user): JWTObject;

    /**
     * @param string                $token
     * @param UsersServiceInterface $usersService
     *
     * @return JWTAuthenticatable|null
     */
    public function getAuthenticatedUser(string $token, UsersServiceInterface $usersService): ?JWTAuthenticatable;

    /**
     * @param JWTAuthenticatable $user
     *
     * @return JWTAuthenticatable
     */
    public function deauthenticate(JWTAuthenticatable $user): JWTAuthenticatable;
}