<?php

namespace App\Models\Auth;

use App\Models\ModelInterface;
use App\Models\Timestampable;
use App\Models\User\UserModelInterface;

/**
 * Interface LoginRefreshTokenModelInterface
 *
 * @package App\Models\Auth
 */
interface LoginRefreshTokenModelInterface extends ModelInterface, Timestampable
{

    const PROPERTY_TOKEN = 'token';
    const PROPERTY_DISABLED_AT = 'disabledAt';
    const PROPERTY_USER = 'user';

    /**
     * @param string $token
     *
     * @return LoginRefreshTokenModelInterface
     */
    public function setToken(string $token): LoginRefreshTokenModelInterface;

    /**
     * @return string
     */
    public function getToken(): string;

    /**
     * @param \DateTime|null $disabledAt
     *
     * @return LoginRefreshTokenModelInterface
     */
    public function setDisabledAt(?\DateTime $disabledAt): LoginRefreshTokenModelInterface;

    /**
     * @return \DateTime|null
     */
    public function getDisabledAt(): ?\DateTime;

    /**
     * @param UserModelInterface|null $user
     *
     * @return LoginRefreshTokenModelInterface
     */
    public function setUser(?UserModelInterface $user): LoginRefreshTokenModelInterface;

    /**
     * @return UserModelInterface|null
     */
    public function getUser(): ?UserModelInterface;
}