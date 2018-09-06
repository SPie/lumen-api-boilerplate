<?php

use App\Exceptions\Api\InvalidSignatureException;
use App\Exceptions\InvalidConfigurationException;
use App\Http\Middleware\ApiSignature;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * Class ApiSignatureTest
 */
class ApiSignatureTest extends TestCase
{
    //region Tests

    /**
     * @return void
     *
     * @throws InvalidConfigurationException
     */
    public function testValidSignature(): void
    {
        $secret = $this->getFaker()->uuid;
        $timeStamp = (new \DateTime())->getTimestamp();
        $body = [$this->getFaker()->uuid];

        try {
            $this->assertTrue(
                $this->createApiSignature($secret)->handle(
                    $this->createApiRequest($this->createSignature($secret, $timeStamp, $body), $timeStamp, $body),
                    function () {
                        return true;
                    }
                )
            );
        } catch (InvalidSignatureException $e) {
            $this->assertTrue(false);
        }
    }

    /**
     * @return void
     */
    public function testInvalidSignature(): void
    {
        try {
            $this->assertFalse(
                $this->createApiSignature($this->getFaker()->uuid)->handle(
                    $this->createApiRequest(
                        $this->createSignature(
                            $this->getFaker()->uuid,
                            $this->getFaker()->numberBetween(),
                            [$this->getFaker()->uuid]
                        ),
                        $this->getFaker()->numberBetween(),
                        [$this->getFaker()->uuid]
                    ),
                    function () {
                        return true;
                    }
                )
            );
        } catch (InvalidSignatureException $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * @return void
     *
     * @throws InvalidConfigurationException
     */
    public function testExpiredSignature(): void
    {
        $secret = $this->getFaker()->uuid;
        $timeStamp = (new \DateTime('-1 day'))->getTimestamp();
        $body = [$this->getFaker()->uuid];

        try {
            $this->assertFalse(
                $this->createApiSignature($secret)->handle(
                    $this->createApiRequest($this->createSignature($secret, $timeStamp, $body), $timeStamp, $body),
                    function () {
                        return true;
                    }
                )
            );
        } catch (InvalidSignatureException $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * @return void
     *
     * @throws InvalidConfigurationException
     */
    public function testSignatureWithInvalidFutureTimestamp(): void
    {
        $secret = $this->getFaker()->uuid;
        $timeStamp = (new \DateTime('+1 day'))->getTimestamp();
        $body = [$this->getFaker()->uuid];

        try {
            $this->assertFalse(
                $this->createApiSignature($secret)->handle(
                    $this->createApiRequest($this->createSignature($secret, $timeStamp, $body), $timeStamp, $body),
                    function () {
                        return true;
                    }
                )
            );
        } catch (InvalidSignatureException $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * @return void
     *
     * @throws InvalidConfigurationException
     */
    public function testMissingRequiredHeaders(): void
    {
        //missing signature
        try {
            $this->assertFalse($this->createApiSignature($this->getFaker()->uuid)->handle(
                $this->createApiRequest(null, $this->getFaker()->numberBetween(), []),
                function () {
                    return true;
                }
            ));
        } catch (InvalidSignatureException $e) {
            $this->assertTrue(true);
        }

        //missing timestamp
        try {
            $this->assertFalse($this->createApiSignature($this->getFaker()->uuid)->handle(
                $this->createApiRequest($this->getFaker()->uuid, null, []),
                function () {
                    return true;
                }
            ));
        } catch (InvalidSignatureException $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * @return void
     */
    public function testInvalidConstructor(): void
    {
        try {
            new ApiSignature('', ApiSignature::ALGORITHM_SHA_512, 15);

            $this->assertTrue(false);
        } catch (InvalidConfigurationException $e) {
            $this->assertTrue(true);
        }

        try {
            new ApiSignature($this->getFaker()->uuid, $this->getFaker()->uuid, 15);

            $this->assertTrue(false);
        } catch (InvalidConfigurationException $e) {
            $this->assertTrue(true);
        }
    }

    //endregion

    /**
     * @param string|null $secret
     *
     * @return ApiSignature
     *
     * @throws InvalidConfigurationException
     */
    private function createApiSignature(string $secret = null): ApiSignature
    {
        return new ApiSignature(
            $secret ?? $this->getFaker()->uuid,
            ApiSignature::ALGORITHM_SHA_512,
            60
        );
    }

    /**
     * @param string $secret
     * @param string $timestamp
     * @param array  $body
     *
     * @return string
     */
    private function createSignature(string $secret, string $timestamp, array $body): string
    {
        return \base64_encode(\hash_hmac(
            ApiSignature::ALGORITHM_SHA_512,
            $timestamp . \json_encode($body),
            $secret
        ));
    }

    /**
     * @param string|null $signature
     * @param string|null $timestamp
     * @param array       $body
     *
     * @return Request
     */
    protected function createApiRequest(string $signature = null, string $timestamp = null, array $body = []): Request
    {
        $headers = [];

        if (!empty($signature)) {
            $headers[ApiSignature::HEADER_SIGNATURE] = $signature;
        }
        if (!empty($timestamp)) {
            $headers[ApiSignature::HEADER_TIMESTAMP] = $timestamp;
        }

        return Request::createFromBase(SymfonyRequest::create(
            $this->getFaker()->url,
            Request::METHOD_GET,
            $body,
            [],
            [],
            $this->transformHeadersToServerVars($headers)
        ));
    }
}