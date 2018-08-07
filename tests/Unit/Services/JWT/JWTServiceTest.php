<?php

use App\Services\Auth\LoginRefreshTokenServiceInterface;
use Test\AuthHelper;
use Test\DatabaseMigrations;
use Test\ModelHelper;
use Test\TestJWTService\TestJWTService;
use Test\UserHelper;

/**
 * Class JWTServiceTest
 */
class JWTServiceTest extends TestCase
{

    use AuthHelper;
    use DatabaseMigrations;
    use ModelHelper;
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

    //endregion

    /**
     * @param string|null $issuer
     * @param string|null $secret
     * @param int         $expiryHours
     *
     * @return TestJWTService
     */
    private function createJWTService(
        string $issuer = null,
        string $secret = null,
        int $expiryHours = 1
    ): TestJWTService
    {
        return new TestJWTService(
            $this->app->get(LoginRefreshTokenServiceInterface::class),
            $issuer ?: $this->getFaker()->uuid,
            $secret ?: $this->getFaker()->password(),
            $expiryHours
        );
    }
}