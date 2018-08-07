<?php
use App\Http\Controllers\Auth\AuthController;
use App\Models\User\UserModelInterface;
use App\Services\JWT\JWTServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Test\ApiHelper;
use Test\DatabaseMigrations;
use Test\ModelHelper;
use Test\ResponseHelper;
use Test\UserHelper;

/**
 * Class AuthApiCallsTest
 */
class AuthApiCallsTest extends TestCase
{

    use ApiHelper;
    use DatabaseMigrations;
    use ModelHelper;
    use ResponseHelper;
    use UserHelper;

    //region Test actions

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testLogin(): void
    {
        $password = $this->getFaker()->password();

        $response = $this->doApiCall(
            $this->getRouteUrl(AuthController::ROUTE_NAME_LOGIN),
            Request::METHOD_POST,
            [
                UserModelInterface::PROPERTY_EMAIL    => $this->createUsers(
                    1,
                    [UserModelInterface::PROPERTY_PASSWORD => Hash::make($password)]
                )->first()->getEmail(),
                UserModelInterface::PROPERTY_PASSWORD => $password,
            ]
        );

        $this->assertResponseStatus(Response::HTTP_NO_CONTENT);
        $this->assertNotEmpty($this->getCookieValue($response, JWTServiceInterface::AUTHORIZATION_BEARER));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testLoginWithoutCredentials(): void
    {
        $response = $this->doApiCall(
            $this->getRouteUrl(AuthController::ROUTE_NAME_LOGIN),
            Request::METHOD_POST
        );

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertEmpty($this->getCookieValue($response, JWTServiceInterface::AUTHORIZATION_BEARER));

        $responseData = $response->getData(true);

        $this->assertArrayHasKey(UserModelInterface::PROPERTY_EMAIL, $responseData);
        $this->assertEquals('validation.required', \reset($responseData[UserModelInterface::PROPERTY_EMAIL]));
        $this->assertArrayHasKey(UserModelInterface::PROPERTY_PASSWORD, $responseData);
        $this->assertEquals('validation.required', \reset($responseData[UserModelInterface::PROPERTY_PASSWORD]));

    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testLoginWithInvalidEmail(): void
    {
        $response = $this->doApiCall(
            $this->getRouteUrl(AuthController::ROUTE_NAME_LOGIN),
            Request::METHOD_POST,
            [
                UserModelInterface::PROPERTY_EMAIL    => $this->getFaker()->safeEmail,
                UserModelInterface::PROPERTY_PASSWORD => $this->getFaker()->password(),
            ]
        );

        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
        $this->assertEmpty($this->getCookieValue($response, JWTServiceInterface::AUTHORIZATION_BEARER));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testLoginWithInvalidPassword(): void
    {
        $response = $this->doApiCall(
            $this->getRouteUrl(AuthController::ROUTE_NAME_LOGIN),
            Request::METHOD_POST,
            [
                UserModelInterface::PROPERTY_EMAIL    => $this->createUsers()->first()->getEmail(),
                UserModelInterface::PROPERTY_PASSWORD => $this->getFaker()->password(),
            ]
        );

        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
        $this->assertEmpty($this->getCookieValue($response, JWTServiceInterface::AUTHORIZATION_BEARER));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testLogout(): void
    {
        $cookie = $this->createAuthCookie($this->createUsers()->first());

        $response = $this->doApiCall(
            $this->getRouteUrl(AuthController::ROUTE_NAME_LOGOUT),
            Request::METHOD_POST,
            [],
            $cookie
        );

        $this->assertResponseStatus(Response::HTTP_NO_CONTENT);
        $this->assertEmpty($this->getCookieValue($response, JWTServiceInterface::AUTHORIZATION_BEARER));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testLogoutWithoutAuthenticatedUser(): void
    {
        $response = $this->doApiCall(
            $this->getRouteUrl(AuthController::ROUTE_NAME_LOGOUT),
            Request::METHOD_POST
        );

        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
        $this->assertEmpty($this->getCookieValue($response, JWTServiceInterface::AUTHORIZATION_BEARER));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testAuthenticatedUser(): void
    {
        $user = $this->createUsers()->first();

        $response = $this->doApiCall(
            $this->getRouteUrl(AuthController::ROUTE_NAME_USER),
            Request::METHOD_GET,
            [],
            $this->createAuthCookie($user)
        );

        $this->assertResponseOk();
        $this->assertNotEmpty($this->getCookieValue($response, JWTServiceInterface::AUTHORIZATION_BEARER));

        $responseData = $response->getData(true);
        $this->assertArrayHasKey(AuthController::RESPONSE_PARAMETER_USER, $responseData);
        $this->assertEquals($user->toArray(), $responseData[AuthController::RESPONSE_PARAMETER_USER]);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testAuthenticatedUserWithoutUser(): void
    {
        $response = $this->doApiCall(
            $this->getRouteUrl(AuthController::ROUTE_NAME_USER),
            Request::METHOD_GET
        );

        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
        $this->assertEmpty($this->getCookieValue($response, JWTServiceInterface::AUTHORIZATION_BEARER));
    }

    //endregion
}