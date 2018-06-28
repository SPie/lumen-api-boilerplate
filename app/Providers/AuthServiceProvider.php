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
            $token = $request->cookie(JWTServiceInterface::AUTHORIZATION_BEARER);
            if (empty($token)) {
                return null;
            }

            return $this->app->get(JWTServiceInterface::class)->getAuthenticatedUser(
                $token,
                $this->app->get(UsersServiceInterface::class)
            );
        });
    }
}
