<?php

namespace App\Models\User;

use App\Models\ModelInterface;
use App\Models\SoftDeletable;
use App\Models\Timestampable;
use App\Services\JWT\JWTAuthenticatable;

/**
 * Interface UserModelInterface
 *
 * @package App\Models\User
 */
interface UserModelInterface extends ModelInterface, Timestampable, SoftDeletable, JWTAuthenticatable
{

    const PROPERTY_EMAIL    = 'email';
    const PROPERTY_PASSWORD = 'password';

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setEmail(string $email);

    /**
     * @return string
     */
    public function getEmail(): string;

    /**
     * @param string $password
     *
     * @return $this
     */
    public function setPassword(string $password);
}