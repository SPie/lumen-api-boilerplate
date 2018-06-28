<?php

use App\Services\JWT\JWTService;
use App\Services\JWT\JWTServiceInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Cache;
use Test\DatabaseMigrations;
use Test\UserHelper;

/**
 * Class JWTServiceTest
 */
class JWTServiceTest extends TestCase
{

    use DatabaseMigrations;
    use UserHelper;

    //region Tests

    /**
     * @return void
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
     */
    public function testRefreshToken(): void
    {
        $jwtService = $this->createJWTService();

        $user = $this->createUsers()->first();

        $oldJwtObject = $jwtService->createToken($user);

        $jwtObject = $jwtService->refreshAuthToken($user->setJWTObject($oldJwtObject));

        $this->assertNotEmpty($jwtObject->getExpiresAt());
        $this->assertNotEmpty($jwtObject->getToken());
        $this->assertNotEquals($oldJwtObject->getToken(), $jwtObject->getToken());
        $this->assertEquals(3, \count(\explode('.', $jwtObject->getToken())));
        $this->assertNotEmpty(Cache::get(\md5($oldJwtObject->getToken())));
    }

    /**
     * @return void
     */
    public function testRefreshTokenWithoutOldToken(): void
    {
        try {
            $this->createJWTService()->refreshAuthToken($this->createUsers()->first());

            $this->assertTrue(false);
        } catch (AuthorizationException $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * @return void
     */
    public function testGetAuthenticated(): void
    {
        $jwtService = $this->createJWTService();

        $user = $this->createUsers()->first();
        $token = $jwtService->createToken($user);

        $this->assertEquals(
            $user->setJWTObject($token),
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
     */
    public function testGetAuthenticatedWithBlacklistedToken(): void
    {
        $jwtService = $this->createJWTService();

        $token = $jwtService->createToken($this->createUsers()->first());

        Cache::add(\md5($token->getToken()), $token->getToken(), 1);

        $this->assertEmpty($jwtService->getAuthenticatedUser($token->getToken(), $this->getUserService()));

    }

    /**
     * @return void
     */
    public function testDeauthenticate(): void
    {
        $jwtService = $this->createJWTService();

        $user = $this->createUsers()->first();

        $token = $jwtService->createToken($user);

        $user->setJWTObject($token);

        $this->assertEmpty($jwtService->deauthenticate($user)->getJWTObject());
        $this->assertNotEmpty(Cache::get(\md5($token->getToken())));
    }

    /**
     * @return void
     */
    public function testDeauthenticateWithoutToken(): void
    {
        try {
            $this->createJWTService()->deauthenticate($this->createUsers()->first());

            $this->assertTrue(false);
        } catch (AuthorizationException $e) {
            $this->assertTrue(true);
        }
    }

    //endregion

    /**
     * @param string|null $issuer
     * @param string|null $secret
     * @param int         $expiryHours
     *
     * @return JWTServiceInterface
     */
    private function createJWTService(
        string $issuer = null,
        string $secret = null,
        int $expiryHours = 1
    ): JWTServiceInterface
    {
        return new JWTService(
            $issuer ?: $this->getFaker()->uuid,
            $secret ?: $this->getFaker()->password(),
            $expiryHours
        );
    }
}