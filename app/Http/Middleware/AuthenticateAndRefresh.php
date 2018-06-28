<?php

namespace App\Http\Middleware;

use App\Services\JWT\JWTServiceInterface;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Class AuthenticateAndRefresh
 *
 * @package App\Http\Middleware
 */
class AuthenticateAndRefresh extends Authenticate
{

    /**
     * @var JWTServiceInterface
     */
    private $jwtService;

    /**
     * AuthenticateAndRefresh constructor.
     *
     * @param JWTServiceInterface $jwtService
     * @param Auth                $auth
     */
    public function __construct(JWTServiceInterface $jwtService, Auth $auth)
    {
        $this->jwtService = $jwtService;

        parent::__construct($auth);
    }

    /**
     * @return JWTServiceInterface
     */
    protected function getJwtService(): JWTServiceInterface
    {
        return $this->jwtService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, \Closure $next, $guard = null)
    {
        $response = parent::handle($request, $next, $guard);

        if (!($response instanceof JsonResponse) || $response->getStatusCode() == Response::HTTP_UNAUTHORIZED) {
            return $response;
        }

        $jwtObject = $this->getJwtService()->refreshAuthToken($this->getAuth()->guard($guard)->user());

        return $response->withCookie(new Cookie(
            JWTServiceInterface::AUTHORIZATION_BEARER,
            $jwtObject->getToken(),
            $jwtObject->getExpiresAt()
        ));
    }
}