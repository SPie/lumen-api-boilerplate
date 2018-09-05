<?php

use App\Services\JWT\JWTObject;
use App\Services\JWT\Response\HeaderTokenProvider;
use App\Services\JWT\Response\InvalidTokenProviderConfigurationException;
use App\Services\JWT\TokenProviderInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Test\ApiHelper;
use Test\ResponseHelper;

/**
 * Class HeaderResponseProviderTest
 */
class HeaderTokenProviderTest extends TestCase
{

    use ApiHelper;
    use ResponseHelper;

    const BEARER = 'Authorization';

    //region Tests

    /**
     * @return void
     */
    public function testConstructor(): void
    {
        try {
            new HeaderTokenProvider([TokenProviderInterface::CONFIG_BEARER => $this->getFaker()->uuid]);

            $this->assertTrue(true);
        } catch (InvalidTokenProviderConfigurationException $e) {
            $this->assertTrue(false);
        }
    }

    /**
     * @return void
     */
    public function testConstructorException(): void
    {
        try {
            new HeaderTokenProvider([$this->getFaker()->uuid => $this->getFaker()->uuid]);

            $this->assertTrue(false);
        } catch (InvalidTokenProviderConfigurationException $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * @return void
     *
     * @throws InvalidTokenProviderConfigurationException
     */
    public function testHandleRequest(): void
    {
        $token = $this->getFaker()->uuid;

        $this->assertEquals(
            $token,
            $this->createHeaderTokenProvider()->handleRequest(
                $this->createRequest(
                    Request::METHOD_GET,
                    $this->getFaker()->url,
                    [],
                    [],
                    $this->createTokenHeader($token)
                )
            )
        );
    }

    /**
     * @return void
     *
     * @throws InvalidTokenProviderConfigurationException
     */
    public function testHandleEmptyRequest(): void
    {
        $this->assertEmpty(
            $this->createHeaderTokenProvider()->handleRequest(
                $this->createRequest(
                    Request::METHOD_GET,
                    $this->getFaker()->url
                )
            )
        );
    }

    /**
     * @return void
     *
     * @throws InvalidTokenProviderConfigurationException
     */
    public function testHandleResponse(): void
    {
        $token = $this->getFaker()->uuid;

        $this->assertEquals(
            $token,
            $this->getHeaderValue(
                $this->createHeaderTokenProvider()->handleResponse(new JsonResponse(), $this->createJwtObject($token)),
                self::BEARER
            )
        );
    }

    //endregion

    /**
     * @param string $bearer
     *
     * @return HeaderTokenProvider
     *
     * @throws InvalidTokenProviderConfigurationException
     */
    protected function createHeaderTokenProvider(string $bearer = self::BEARER): HeaderTokenProvider
    {
        return new HeaderTokenProvider([TokenProviderInterface::CONFIG_BEARER => $bearer]);
    }

    /**
     * @param string $token
     * @param string $bearer
     *
     * @return array
     */
    protected function createTokenHeader(string $token, string $bearer = self::BEARER): array
    {
        return [
            $bearer => $token,
        ];
    }
}