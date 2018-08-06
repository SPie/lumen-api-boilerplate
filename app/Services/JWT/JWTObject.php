<?php

namespace App\Services\JWT;

/**
 * Class JWTObject
 *
 * @package App\Services\JWT
 */
class JWTObject
{

    /**
     * @var string
     */
    private $token;

    /**
     * @var \DateTime|null
     */
    private $expiresAt;

    /**
     * @var string|null
     */
    private $refreshToken;

    /**
     * JWTObject constructor.
     *
     * @param string         $token
     * @param \DateTime|null $expiresAt
     * @param string|null    $refreshToken
     */
    public function __construct(string $token, ?\DateTime $expiresAt, string $refreshToken = null)
    {
        $this->token = $token;
        $this->expiresAt = $expiresAt;
        $this->refreshToken = $refreshToken;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return \DateTime|null
     */
    public function getExpiresAt(): ?\DateTime
    {
        return $this->expiresAt;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }
}