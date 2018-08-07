<?php

namespace App\Repositories\Auth;

use App\Models\Auth\LoginRefreshTokenModelInterface;
use App\Models\ModelInterface;
use App\Repositories\AbstractDoctrineRepository;

/**
 * Class LoginRefreshTokenDoctrineRepository
 *
 * @package App\Repositories\Auth
 */
class LoginRefreshTokenDoctrineRepository extends AbstractDoctrineRepository implements LoginRefreshTokenRepositoryInterface
{

    /**
     * @param string $token
     *
     * @return LoginRefreshTokenModelInterface|ModelInterface|null
     */
    public function findOneByToken(string $token): ?LoginRefreshTokenModelInterface
    {
        return $this->findOneBy([
            LoginRefreshTokenModelInterface::PROPERTY_TOKEN => $token
        ]);
    }
}