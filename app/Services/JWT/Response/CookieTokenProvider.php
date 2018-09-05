<?php

namespace App\Services\JWT\Response;

use App\Services\JWT\JWTObject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Class CookieTokenProvider
 *
 * @package App\Services\JWT\Response
 */
class CookieTokenProvider extends AbstractTokenProvider
{

    /**
     * @param Request $request
     *
     * @return string|null
     */
    public function handleRequest(Request $request): ?string
    {
        foreach ($request->cookies as $cookie) {
            if ($cookie->getName() == $this->getBearer()) {
                return $cookie->getValue();
            }
        }

        return null;
    }

    /**
     * @param JsonResponse  $response
     * @param JWTObject $jwTObject
     *
     * @return JsonResponse
     */
    public function handleResponse(JsonResponse $response, JWTObject $jwTObject): JsonResponse
    {
        return $response->withCookie(new Cookie(
            $this->getBearer(),
            $jwTObject->getToken(),
            $jwTObject->getExpiresAt()
        ));
    }
}