<?php

use App\Services\JWT\Response\CookieTokenProvider;
use App\Services\JWT\Response\InvalidTokenProviderConfigurationException;
use App\Services\JWT\TokenProviderInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Test\ApiHelper;
use Test\ResponseHelper;

/**
 * Class CookieTokenProviderTest
 */
class CookieTokenProviderTest extends TestCase
{

    const BEARER = 'Authorization';

    use ApiHelper;
    use ResponseHelper;

    //region Tests

    /**
     * @return void
     */
    public function testConstructor(): void
    {
        try {
            new CookieTokenProvider([TokenProviderInterface::CONFIG_BEARER => $this->getFaker()->uuid]);

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
            new CookieTokenProvider([$this->getFaker()->uuid => $this->getFaker()->uuid]);

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
            $this->createCookieTokenProvider()->handleRequest(
                $this->createRequest(
                    Request::METHOD_GET,
                    $this->getFaker()->url,
                    [],
                    [
                        $this->createTokenCookie($token)
                    ]
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
            $this->createCookieTokenProvider()->handleRequest(
                $this->createRequest(
                    Request::METHOD_GET,
                    $this->getFaker()->url,
                    [],
                    [
                        $this->createTokenCookie($this->getFaker()->uuid, null, $this->getFaker()->uuid)
                    ]
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
            $this->getCookieValue(
                $this->createCookieTokenProvider()->handleResponse(new JsonResponse(), $this->createJwtObject($token)),
                self::BEARER
            )
        );
    }

    //endregion

    /**
     * @param string $bearer
     *
     * @return CookieTokenProvider
     *
     * @throws InvalidTokenProviderConfigurationException
     */
    protected function createCookieTokenProvider(string $bearer = self::BEARER): CookieTokenProvider
    {
        return new CookieTokenProvider([TokenProviderInterface::CONFIG_BEARER => $bearer]);
    }

    /**
     * @param string        $token
     * @param DateTime|null $expiry
     * @param string        $bearer
     *
     * @return Cookie
     */
    protected function createTokenCookie(string $token, \DateTime $expiry = null, string $bearer = self::BEARER): Cookie
    {
        $expiry = $expiry ?: new \DateTime();

        return new Cookie(
            $bearer,
            $token,
            $expiry
        );
    }
}