<?php

namespace App\Providers;

use App\Models\User\UserDoctrineModel;
use App\Models\User\UserDoctrineModelFactory;
use App\Models\User\UserModelFactoryInterface;
use App\Models\User\UserModelInterface;
use App\Repositories\User\UserRepositoryInterface;
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
            ->registerServices();
    }

    /**
     * @return $this
     */
    private function registerModels()
    {
        $this->app->bind(UserModelInterface::class, UserDoctrineModel::class);

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

        return $this;
    }

    /**
     * @return $this
     */
    private function registerModelFactories()
    {
        $this->app->singleton(UserModelFactoryInterface::class, UserDoctrineModelFactory::class);

        return $this;
    }

    /**
     * @return $this
     */
    private function registerServices()
    {
        $this->app->singleton(JWTServiceInterface::class, function () {
            return new JWTService(
                $this->app['config']['services.jwt.issuer'],
                $this->app['config']['services.jwt.secret'],
                $this->app['config']['services.jwt.expiryHours']
            );
        });

        $this->app->singleton(UsersServiceInterface::class, function () {
            return new UsersService(
                $this->app->get(UserRepositoryInterface::class),
                $this->app->get(UserModelFactoryInterface::class)
            );
        });

        return $this;
    }

    //endregion
}
