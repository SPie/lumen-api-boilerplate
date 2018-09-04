<?php

namespace App\Services\JWT\Response;

use App\Services\JWT\JWTObject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class HeaderTokenProvider
 *
 * @package App\Services\JWT\Response
 */
class HeaderTokenProvider extends AbstractTokenProvider
{

    /**
     * @param Request $request
     *
     * @return string|null
     */
    public function handleRequest(Request $request): ?string
    {
        return $request->header($this->getBearer());
    }

    /**
     * @param JsonResponse  $response
     * @param JWTObject $jwTObject
     *
     * @return JsonResponse
     */
    public function handleResponse(JsonResponse $response, JWTObject $jwTObject): JsonResponse
    {
        return $response->header($this->getBearer(), $jwTObject->getToken());
    }
}