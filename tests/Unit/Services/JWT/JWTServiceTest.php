<?php

use App\Services\Auth\LoginRefreshTokenServiceInterface;
use App\Services\JWT\Response\CookieTokenProvider;
use App\Services\JWT\Response\InvalidTokenProviderConfigurationException;
use App\Services\JWT\TokenProviderInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LaravelDoctrine\Migrations\Testing\DatabaseMigrations;
use Symfony\Component\HttpFoundation\Cookie;
use Test\ApiHelper;
use Test\AuthHelper;
use Test\ModelHelper;
use Test\ResponseHelper;
use Test\TestJWTService\TestJWTService;
use Test\UserHelper;

/**
 * Class JWTServiceTest
 */
class JWTServiceTest extends TestCase
{

    use ApiHelper;
    use AuthHelper;
    use DatabaseMigrations;
    use ModelHelper;
    use ResponseHelper;
    use UserHelper;

    //region Tests

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCreateToken(): void
    {
        $jwtObject = $this->createJWTService()->createToken($this->createUsers()->first());

        $this->assertNotEmpty($jwtObject->getExpiresAt());
        $this->assertNotEmpty($jwtObject->getToken());
        $this->assertEquals(3, \count(\explode('.', $jwtObject->getToken())));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testRefreshToken(): void
    {
        $jwtService = $this->createJWTService();

        $user = $this->createUsers()->first();

        $oldJwtObject = $jwtService->createToken($user);

        $jwtObject = $jwtService->refreshAuthToken($user->setUsedJWTRefreshToken($oldJwtObject->getRefreshToken()));

        $this->assertNotEmpty($jwtObject->getExpiresAt());
        $this->assertNotEmpty($jwtObject->getToken());
        $this->assertNotEquals($oldJwtObject->getToken(), $jwtObject->getToken());
        $this->assertEquals(3, \count(\explode('.', $jwtObject->getToken())));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetAuthenticated(): void
    {
        $jwtService = $this->createJWTService();

        $user = $this->createUsers()->first();
        $token = $jwtService->createToken($user);

        $this->assertEquals(
            $user->setUsedJWTRefreshToken($token->getRefreshToken()),
            $jwtService->getAuthenticatedUser($token->getToken(), $this->getUserService())
        );
    }

    /**
     * @return void
     *
     * @throws InvalidTokenProviderConfigurationException
     */
    public function testGetAuthenticatedWithInvalidToken(): void
    {
        $this->assertEmpty(
            $this->createJWTService()->getAuthenticatedUser($this->getFaker()->uuid, $this->getUserService())
        );
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetAuthenticatedWithoutUser(): void
    {
        $user = $this->createUsers()->first();

        $jwtService = $this->createJWTService();

        $token = $jwtService->createToken($user);

        $this->getUserRepository()->delete($user);

        $this->assertEmpty($jwtService->getAuthenticatedUser($token->getToken(), $this->getUserService()));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetAuthenticatedWithRefreshedToken(): void
    {
        $jwtService = $this->createJWTService()->setExpiredTimeStamps(true);

        $token = $jwtService->createToken($this->createUsers()->first());

        $this->assertNotEmpty($jwtService->getAuthenticatedUser($token->getToken(), $this->getUserService()));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetAuthenticatedWithBlacklistedToken(): void
    {
        $jwtService = $this->createJWTService()->setExpiredTimeStamps(true);

        $token = $jwtService->createToken($this->createUsers()->first());

        $this->getLoginRefreshTokenRepository()->save(
            $this->getLoginRefreshTokenRepository()->findOneByToken(
                $token->getRefreshToken())->setDisabledAt(new \DateTime()
            )
        );

        $this->assertEmpty($jwtService->getAuthenticatedUser($token->getToken(), $this->getUserService()));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetAuthenticatedWithoutRefreshToken(): void
    {
        $jwtService = $this->createJWTService()->setExpiredTimeStamps(true);

        $token = $jwtService->createToken($this->createUsers()->first(), false);

        $this->assertEmpty($jwtService->getAuthenticatedUser($token->getToken(), $this->getUserService()));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testDeauthenticate(): void
    {
        $jwtService = $this->createJWTService();

        $user = $this->createUsers()->first();

        $token = $jwtService->createToken($user);

        $user->setUsedJWTRefreshToken($token->getRefreshToken());

        $this->assertEmpty($jwtService->deauthenticate($user)->getUsedJWTRefreshToken());
        $this->assertNotEmpty(
            $this->getLoginRefreshTokenRepository()->findOneByToken($token->getRefreshToken())->getDisabledAt()
        );
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
            $this->createJWTService()->handleRequest(
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
    public function testHandleRequestWithoutToken(): void
    {
        $this->assertEmpty(
            $this->createJWTService()->handleRequest(
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
    public function testResponse(): void
    {
        $token = $this->getFaker()->uuid;

        $this->assertEquals(
            $token,
            $this->getCookieValue(
                $this->createJWTService()->response(new JsonResponse(), $this->createJwtObject($token)),
                'Authorization'
            )
        );
    }

    //endregion

    /**
     * @param string|null $issuer
     * @param string|null $secret
     * @param int         $expiryHours
     *
     * @return TestJWTService
     *
     * @throws \App\Services\JWT\Response\InvalidTokenProviderConfigurationException
     */
    private function createJWTService(
        string $issuer = null,
        string $secret = null,
        int $expiryHours = 1
    ): TestJWTService
    {
        return new TestJWTService(
            $this->app->get(LoginRefreshTokenServiceInterface::class),
            new CookieTokenProvider([TokenProviderInterface::CONFIG_BEARER => 'Authorization']),
            $issuer ?: $this->getFaker()->uuid,
            $secret ?: $this->getFaker()->password(),
            $expiryHours
        );
    }

    /**
     * @param string        $token
     * @param DateTime|null $expiry
     * @param string        $bearer
     *
     * @return Cookie
     */
    private function createTokenCookie(string $token, \DateTime $expiry = null, string $bearer = 'Authorization'): Cookie
    {
        $expiry = $expiry ?: new \DateTime();

        return new Cookie(
            $bearer,
            $token,
            $expiry
        );
    }
}