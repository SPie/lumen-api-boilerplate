<?php

namespace Test;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Trait ResponseHelper
 *
 * @package Test
 */
trait ResponseHelper
{

    /**
     * @param JsonResponse $response
     * @param string       $headerName
     *
     * @return null|string
     */
    protected function getHeaderValue(JsonResponse $response, string $headerName): ?string
    {
        return $response->headers->get($headerName);
    }

    /**
     * @param JsonResponse $response
     * @param string       $cookieName
     *
     * @return null|string
     */
    protected function getCookieValue(JsonResponse $response, string $cookieName): ?string
    {
        /** @var Cookie[] $cookies */
        $cookies = $response->headers->getCookies();
        foreach ($cookies as $cookie) {
            if ($cookie->getName() == $cookieName) {
                return $cookie->getValue();
            }
        }

        return null;
    }
}