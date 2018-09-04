<?php

namespace App\Services\JWT;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Interface TokenProviderInterface
 *
 * @package App\Services\JWT
 */
interface TokenProviderInterface
{

    const CONFIG_BEARER = 'bearer';

    /**
     * @param Request $request
     *
     * @return string|null
     */
    public function handleRequest(Request $request): ?string;

    /**
     * @param JsonResponse  $response
     * @param JWTObject $jwTObject
     *
     * @return JsonResponse
     */
    public function handleResponse(JsonResponse $response, JWTObject $jwTObject): JsonResponse;
}