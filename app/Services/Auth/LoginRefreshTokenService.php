<?php

namespace App\Services\Auth;

use App\Models\Auth\LoginRefreshTokenModelFactoryInterface;
use App\Models\Auth\LoginRefreshTokenModelInterface;
use App\Models\User\UserModelInterface;
use App\Repositories\Auth\LoginRefreshTokenRepositoryInterface;

/**
 * Class LoginRefreshTokenService
 *
 * @package App\Services\Auth
 */
class LoginRefreshTokenService implements LoginRefreshTokenServiceInterface
{

    /**
     * @var LoginRefreshTokenRepositoryInterface
     */
    private $loginRefreshTokenRepository;

    /**
     * @var LoginRefreshTokenModelFactoryInterface
     */
    private $loginRefreshTokenModelFactory;

    /**
     * LoginRefreshTokenService constructor.
     *
     * @param LoginRefreshTokenRepositoryInterface   $loginRefreshTokenRepository
     * @param LoginRefreshTokenModelFactoryInterface $loginRefreshTokenModelFactory
     */
    public function __construct(
        LoginRefreshTokenRepositoryInterface $loginRefreshTokenRepository,
        LoginRefreshTokenModelFactoryInterface $loginRefreshTokenModelFactory
    )
    {
        $this->loginRefreshTokenRepository = $loginRefreshTokenRepository;
        $this->loginRefreshTokenModelFactory = $loginRefreshTokenModelFactory;
    }

    /**
     * @return LoginRefreshTokenRepositoryInterface
     */
    protected function getLoginRefreshTokenRepository(): LoginRefreshTokenRepositoryInterface
    {
        return $this->loginRefreshTokenRepository;
    }

    /**
     * @return LoginRefreshTokenModelFactoryInterface
     */
    protected function getLoginRefreshTokenModelFactory(): LoginRefreshTokenModelFactoryInterface
    {
        return $this->loginRefreshTokenModelFactory;
    }

    /**
     * @param UserModelInterface $user
     *
     * @return LoginRefreshTokenModelInterface
     */
    public function createLoginRefreshToken(UserModelInterface $user): LoginRefreshTokenModelInterface
    {
        return $this->getLoginRefreshTokenRepository()->save(
            $this->getLoginRefreshTokenModelFactory()->create([
                LoginRefreshTokenModelInterface::PROPERTY_TOKEN => $this->createToken($user),
                LoginRefreshTokenModelInterface::PROPERTY_USER  => $user,
            ])
        );
    }

    /**
     * @param string $token
     *
     * @return UserModelInterface|null
     */
    public function useLoginRefreshToken(string $token): ?UserModelInterface
    {
        $loginRefreshToken = $this->getLoginRefreshTokenRepository()->findOneByToken($token);
        if (!$loginRefreshToken || !empty($loginRefreshToken->getDisabledAt())) {
            return null;
        }

        return $this->getLoginRefreshTokenRepository()->save(
            $loginRefreshToken->setDisabledAt(new \DateTime())
        )->getUser();
    }

    /**
     * @param string $token
     *
     * @return LoginRefreshTokenServiceInterface
     */
    public function disableRefreshToken(string $token): LoginRefreshTokenServiceInterface
    {
        $this->getLoginRefreshTokenRepository()->save(
            $this->getLoginRefreshTokenRepository()->findOneByToken($token)->setDisabledAt(new \DateTime())
        );

        return $this;
    }

    /**
     * @param UserModelInterface $user
     *
     * @return string
     */
    protected function createToken(UserModelInterface $user): string
    {
        return \md5($user->getId() . \mt_rand() . \time());
    }
}