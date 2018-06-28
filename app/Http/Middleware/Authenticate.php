<?php

namespace App\Http\Middleware;

use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Http\Response;
use Laravel\Lumen\Http\ResponseFactory;

/**
 * Class Authenticate
 *
 * @package App\Http\Middleware
 */
class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    private $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  Auth $auth
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @return Auth
     */
    protected function getAuth(): Auth
    {
        return $this->auth;
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
        if ($this->getAuth()->guard($guard)->guest()) {
            return (new ResponseFactory())->json(['Unauthorized.'], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
