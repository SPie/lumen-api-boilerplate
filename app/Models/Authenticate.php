<?php

namespace App\Models;

use Illuminate\Support\Facades\Hash;

/**
 * Trait Authenticate
 *
 * @package App\Models
 */
trait Authenticate
{

    /**
     * @ORM\Column(name="password", type="string", length=255, nullable=false)
     *
     * @var string|null
     */
    protected $password;

    /**
     * Get the column name for the primary key
     *
     * @return string
     */
    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    /**
     * Get the unique identifier for the user.
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        $name = $this->getAuthIdentifierName();

        return $this->{$name};
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return $this
     */
    public function setPassword(string $password)
    {
        $this->password = Hash::make($password);

        return $this;
    }

    /**
     * Get the password for the user.
     * @return string
     */
    public function getAuthPassword(): string
    {
        return $this->getPassword();
    }

    /**
     * Get the token value for the "remember me" session.
     * @return string
     */
    public function getRememberToken(): ?string
    {
        return null;
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param string $value
     *
     * @return void
     */
    public function setRememberToken($value): void
    {
    }

    /**
     * Get the column name for the "remember me" token.
     * @return string
     */
    public function getRememberTokenName(): string
    {
        return 'rememberToken';
    }
}