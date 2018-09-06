<?php

namespace Test;

use App\Http\Middleware\ApiSignature;
use App\Models\User\UserModelInterface;
use App\Services\JWT\JWTObject;
use App\Services\JWT\TokenProviderInterface;
use App\Services\JWT\JWTServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

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
     * @param array       $headers
     *
     * @return JsonResponse
     */
    protected function doApiCall(
        string $uri,
        string $method = Request::METHOD_GET,
        array $parameters = [],
        Cookie $authToken = null,
        array $headers = []
    ): JsonResponse
    {
        $cookies = [];
        if (!empty($authToken)) {
            $cookies[$authToken->getName()] = $authToken->getValue();
        }

        $timestamp = (new \DateTime())->getTimestamp();

        return $this->call(
            $method,
            $uri,
            $parameters,
            $cookies,
            [],
            $this->transformHeadersToServerVars(
                \array_merge(
                    [
                        ApiSignature::HEADER_SIGNATURE => $this->createSignature($timestamp, $parameters),
                        ApiSignature::HEADER_TIMESTAMP => $timestamp,
                    ],
                    $headers
                )
            )
        );
    }

    /**
     * @param string $timestamp
     * @param array  $parameters
     *
     * @return string
     */
    protected function createSignature(string $timestamp, array $parameters): string
    {
        return \base64_encode(\hash_hmac(
            ApiSignature::ALGORITHM_SHA_512,
            $timestamp . \json_encode($parameters),
            $this->app['config']['middlewares.apiSignature.secret']
        ));
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array  $parameters
     * @param array  $cookies
     * @param array  $headers
     *
     * @return Request
     */
    protected function createRequest(
        string $method = Request::METHOD_GET,
        string $uri = '',
        array $parameters = [],
        array $cookies = [],
        array $headers = []
    ): Request
    {
        return Request::createFromBase(SymfonyRequest::create(
            $this->prepareUrlForRequest($uri),
            $method,
            $parameters,
            $cookies,
            [],
            $this->transformHeadersToServerVars($headers)
        ));
    }

    /**
     * @param UserModelInterface $user
     *
     * @return array
     */
    protected function createAuthHeader(UserModelInterface $user): array
    {
        return [
            'Authorization' => $this->createJWTToken($user)->getToken(),
        ];
    }

    /**
     * @param UserModelInterface $user
     *
     * @return Cookie
     */
    protected function createAuthCookie(UserModelInterface $user): Cookie
    {
        $token = $this->createJWTToken($user);

        return new Cookie(
            TokenProviderInterface::CONFIG_BEARER,
            $token->getToken(),
            $token->getExpiresAt()
        );
    }

    /**
     * @param string   $token
     * @param \DateTime $expiry
     *
     * @return JWTObject
     */
    protected function createJwtObject(string $token, \DateTime $expiry = null): JWTObject
    {
        $expiry = $expiry ?: new \DateTime();

        return new JWTObject($token, $expiry);
    }

    /**
     * @param UserModelInterface $user
     *
     * @return JWTObject
     */
    protected function createJWTToken(UserModelInterface $user): JWTObject
    {
        /** @var JWTServiceInterface $jwtService */
        $jwtService = $this->app->get(JWTServiceInterface::class);

        return $jwtService->createToken($user);
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