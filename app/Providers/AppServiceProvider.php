<?php

namespace App\Providers;

use App\Http\Middleware\ApiSignature;
use App\Models\Auth\LoginRefreshTokenDoctrineModel;
use App\Models\Auth\LoginRefreshTokenDoctrineModelFactory;
use App\Models\Auth\LoginRefreshTokenModelFactoryInterface;
use App\Models\Auth\LoginRefreshTokenModelInterface;
use App\Models\User\UserDoctrineModel;
use App\Models\User\UserDoctrineModelFactory;
use App\Models\User\UserModelFactoryInterface;
use App\Models\User\UserModelInterface;
use App\Repositories\Auth\LoginRefreshTokenRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\Auth\LoginRefreshTokenService;
use App\Services\Auth\LoginRefreshTokenServiceInterface;
use App\Services\JWT\JWTService;
use App\Services\JWT\JWTServiceInterface;
use App\Services\User\UsersService;
use App\Services\User\UsersServiceInterface;
use Doctrine\ORM\EntityManager;
use Illuminate\Support\ServiceProvider;

/**
 * Class AppServiceProvider
 *
 * @package App\Providers
 */
class AppServiceProvider extends ServiceProvider
{

    //region Register services

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this
            ->registerModels()
            ->registerRepositories()
            ->registerModelFactories()
            ->registerServices()
            ->registerMiddlewares();
    }

    /**
     * @return $this
     */
    private function registerModels()
    {
        $this->app->bind(UserModelInterface::class, UserDoctrineModel::class);
        $this->app->bind(LoginRefreshTokenModelInterface::class, LoginRefreshTokenDoctrineModel::class);

        return $this;
    }

    /**
     * @return $this
     */
    private function registerRepositories()
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->app->get(EntityManager::class);

        $this->app->singleton(UserRepositoryInterface::class, function () use ($entityManager) {
            return $entityManager->getRepository(UserDoctrineModel::class);
        });

        $this->app->singleton(LoginRefreshTokenRepositoryInterface::class, function () use ($entityManager) {
            return $entityManager->getRepository(LoginRefreshTokenDoctrineModel::class);
        });

        return $this;
    }

    /**
     * @return $this
     */
    private function registerModelFactories()
    {
        $this->app->singleton(UserModelFactoryInterface::class, UserDoctrineModelFactory::class);
        $this->app->singleton(
            LoginRefreshTokenModelFactoryInterface::class,
            LoginRefreshTokenDoctrineModelFactory::class
        );
        return $this;
    }

    /**
     * @return $this
     */
    private function registerServices()
    {
        $this->app->singleton(JWTServiceInterface::class, function () {
            $responseProviderClass = $this->app['config']['services.jwt.responseProvider.class'];

            return new JWTService(
                $this->app->get(LoginRefreshTokenServiceInterface::class),
                new $responseProviderClass($this->app['config']['services.jwt.responseProvider.config']),
                $this->app['config']['services.jwt.issuer'],
                $this->app['config']['services.jwt.secret'],
                $this->app['config']['services.jwt.expiryHours']
            );
        });

        $this->app->singleton(UsersServiceInterface::class, UsersService::class);
        $this->app->singleton(LoginRefreshTokenServiceInterface::class, LoginRefreshTokenService::class);

        return $this;
    }

    /**
     * @return $this
     */
    private function registerMiddlewares()
    {
        $this->app->bind(ApiSignature::class, function () {
            return new ApiSignature(
                $this->app['config']['middlewares.apiSignature.secret'],
                $this->app['config']['middlewares.apiSignature.algorithm'],
                $this->app['config']['middlewares.apiSignature.toleranceSeconds']
            );
        });

        return $this;
    }

    //endregion

    //region Boot services

    /**
     * @return void
     */
    public function boot()
    {
        $this->bootModelFactories();
    }

    /**
     * @return AppServiceProvider
     */
    private function bootModelFactories(): AppServiceProvider
    {
        $userModelFactory = $this->app->get(UserModelFactoryInterface::class);
        $loginRefreshTokenFactory = $this->app->get(LoginRefreshTokenModelFactoryInterface::class);

        $userModelFactory->setLoginRefreshTokenModelFactory($loginRefreshTokenFactory);
        $loginRefreshTokenFactory->setUserModelFactory($userModelFactory);

        return $this;
    }

    //endregion
}
