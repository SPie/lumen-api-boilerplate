<?php

namespace Test\TestJWTService;

use App\Services\JWT\JWTService;

/**
 * Class TestJWTService
 *
 * @package Test\TestJWTService
 */
class TestJWTService extends JWTService
{

    /**
     * @var bool
     */
    private $expiredTimeStamps = false;

    /**
     * @param bool $expiredTimeStamps
     *
     * @return JWTService
     */
    public function setExpiredTimeStamps(bool $expiredTimeStamps): JWTService
    {
        $this->expiredTimeStamps = $expiredTimeStamps;

        return $this;
    }

    /**
     * @return bool
     */
    protected function getExpiredTimeStamps(): bool
    {
        return $this->expiredTimeStamps;
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function createTimestamps(): array
    {
        if ($this->getExpiredTimeStamps()) {
            $issuedAt = new \DateTime('-1 day');

            return [
                $issuedAt,
                (clone $issuedAt)->add(new \DateInterval('PT' . $this->getExpiryHours() . 'H')),
            ];
        }

        return parent::createTimestamps();
    }
}