<?php

namespace App\Services\JWT\Response;

use App\Services\JWT\TokenProviderInterface;

/**
 * Class AbstractTokenProvider
 *
 * @package App\Services\JWT\Response
 */
abstract class AbstractTokenProvider implements TokenProviderInterface
{

    /**
     * @var string
     */
    private $bearer;

    /**
     * HeaderResponseProvider constructor.
     *
     * @param array $config
     *
     * @throws InvalidTokenProviderConfigurationException
     */
    public function __construct(array $config)
    {
        if (empty($config[self::CONFIG_BEARER])) {
            throw new InvalidTokenProviderConfigurationException();
        }

        $this->bearer = $config[self::CONFIG_BEARER];
    }

    /**
     * @return string
     */
    protected function getBearer(): string
    {
        return $this->bearer;
    }
}