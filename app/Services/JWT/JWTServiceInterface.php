<?php

namespace App\Services\JWT;

use App\Services\User\UsersServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Interface JWTServiceInterface
 *
 * @package App\Services\JWT
 */
interface JWTServiceInterface
{

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

    /**
     * @param Request $request
     *
     * @return string
     */
    public function handleRequest(Request $request): string;

    /**
     * @param JsonResponse  $response
     * @param JWTObject $jwTObject
     *
     * @return JsonResponse
     */
    public function response(JsonResponse $response, JWTObject $jwTObject): JsonResponse;
}