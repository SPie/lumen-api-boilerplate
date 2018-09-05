<?php

namespace App\Providers;

use App\Services\JWT\JWTServiceInterface;
use App\Services\User\UsersServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

/**
 * Class AuthServiceProvider
 *
 * @package App\Providers
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['auth']->viaRequest('api', function (Request $request) {
            $jwtService = $this->app->get(JWTServiceInterface::class);

            $token = $jwtService->handleRequest($request);
            if (empty($token)) {
                return null;
            }

            return $jwtService->getAuthenticatedUser(
                $token,
                $this->app->get(UsersServiceInterface::class)
            );
        });
    }
}
