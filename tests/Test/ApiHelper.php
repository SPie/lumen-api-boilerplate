<?php

namespace Test;

use App\Models\User\UserModelInterface;
use App\Services\JWT\JWTServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Trait ApiHelper
 *
 * @package Test
 */
trait ApiHelper
{

    /**
     * @param string      $uri
     * @param string      $method
     * @param array       $parameters
     * @param Cookie|null $authToken
     *
     * @return JsonResponse
     */
    protected function doApiCall(
        string $uri,
        string $method = Request::METHOD_GET,
        array $parameters = [],
        Cookie $authToken = null
    ): JsonResponse
    {
        $cookies = [];
        if (!empty($authToken)) {
            $cookies[$authToken->getName()] = $authToken->getValue();
        }

        return $this->call(
            $method,
            $uri,
            $parameters,
            $cookies,
            [],
            $this->transformHeadersToServerVars([])
        );
    }

    /**
     * @param UserModelInterface $user
     *
     * @return Cookie
     */
    protected function createAuthCookie(UserModelInterface $user): Cookie
    {
        /** @var JWTServiceInterface $jwtService */
        $jwtService = $this->app->get(JWTServiceInterface::class);

        $token = $jwtService->createToken($user);

        return new Cookie(
            JWTServiceInterface::AUTHORIZATION_BEARER,
            $token->getToken(),
            $token->getExpiresAt()
        );
    }

    /**
     * @param string $routeName
     *
     * @param array  $parameters
     *
     * @return string
     *
     * @throws \Exception
     */
    protected function getRouteUrl(string $routeName, array $parameters = []): string
    {
        if (!isset($this->app->router->namedRoutes[$routeName])) {
            throw new \Exception();
        }

        return \preg_replace_callback(
            '/\{(.*?)(:.*?)?(\{[0-9,]+\})?\}/',
            function ($m) use (&$parameters) {
                return isset($parameters[$m[1]]) ? array_pull($parameters, $m[1]) : $m[0];
            },
            $this->app->router->namedRoutes[$routeName]
        );
    }
}