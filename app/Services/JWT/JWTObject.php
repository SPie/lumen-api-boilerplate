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
     * JWTObject constructor.
     *
     * @param string         $token
     * @param \DateTime|null $expiresAt
     */
    public function __construct(string $token, ?\DateTime $expiresAt)
    {
        $this->token = $token;
        $this->expiresAt = $expiresAt;
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
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }
}