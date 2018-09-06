<?php

namespace App\Http\Middleware;

use App\Exceptions\Api\InvalidSignatureException;
use App\Exceptions\InvalidConfigurationException;
use Illuminate\Http\Request;

/**
 * Class ApiSignature
 *
 * @package App\Http\Middleware
 */
class ApiSignature
{

    const ALGORITHM_SHA_512 = 'sha512';

    const HEADER_SIGNATURE = 'x-signature';
    const HEADER_TIMESTAMP = 'x-timestamp';

    /**
     * @var string[]
     */
    private $supportedAlgorithms = [
        self::ALGORITHM_SHA_512,
    ];

    /**
     * @var string
     */
    private $secret;

    /**
     * @var string
     */
    private $algorithm;

    /**
     * @var int
     */
    private $toleranceSeconds;

    /**
     * ApiSignature constructor.
     *
     * @param string $secret
     * @param string $algorithm
     * @param int    $toleranceSeconds
     *
     * @throws InvalidConfigurationException
     */
    public function __construct(string $secret, string $algorithm, int $toleranceSeconds = 0)
    {
        if (empty($secret)) {
            throw new InvalidConfigurationException('Secret cannot be empty.');
        }
        if (!\in_array($algorithm, $this->getSupportedAlgorithms())) {
            throw new InvalidConfigurationException('Algorithm is not supported.');
        }

        $this->secret = $secret;
        $this->algorithm = $algorithm;
        $this->toleranceSeconds = $toleranceSeconds;
    }

    /**
     * @return string[]
     */
    protected function getSupportedAlgorithms(): array
    {
        return $this->supportedAlgorithms;
    }

    /**
     * @return string
     */
    protected function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * @return string
     */
    protected function getAlgorithm(): string
    {
        return $this->algorithm;
    }

    /**
     * @return int
     */
    protected function getToleranceSeconds(): int
    {
        return $this->toleranceSeconds;
    }

    /**
     * @param Request  $request
     * @param \Closure $next
     *
     * @return mixed
     *
     * @throws InvalidSignatureException
     */
    public function handle(Request $request, \Closure $next)
    {
        if (
            empty($request->header(self::HEADER_SIGNATURE))
            || empty($request->header(self::HEADER_TIMESTAMP))
        ) {
            throw new InvalidSignatureException('Required headers are missing.');
        }

        $signature = $request->header(self::HEADER_SIGNATURE);
        $timestamp = (int)$request->header(self::HEADER_TIMESTAMP);

        $now = (new \DateTime())->getTimestamp();
        if ($timestamp < ($now - $this->getToleranceSeconds()) || $timestamp > ($now + $this->getToleranceSeconds())) {
            throw new InvalidSignatureException('Signature is expired');
        }

        if ($signature !== $this->createSignature($timestamp, $request->all())) {
            throw new InvalidSignatureException('Invalid Signature');
        }

        return $next($request);
    }

    /**
     * @param string $timestamp
     * @param array  $body
     *
     * @return string
     */
    protected function createSignature(string $timestamp, array $body): string
    {
        return \base64_encode(\hash_hmac(
            $this->getAlgorithm(),
            $timestamp . \json_encode($body),
            $this->getSecret()
        ));
    }
}