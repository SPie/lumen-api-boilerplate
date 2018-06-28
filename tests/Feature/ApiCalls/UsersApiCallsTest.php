<?php
use App\Http\Controllers\User\UsersController;
use App\Models\User\UserModelInterface;
use App\Services\JWT\JWTServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Test\ApiHelper;
use Test\DatabaseMigrations;
use Test\ResponseHelper;
use Test\UserHelper;

/**
 * Class UsersApiCallsTest
 */
class UsersApiCallsTest extends TestCase
{

    use ApiHelper;
    use DatabaseMigrations;
    use ResponseHelper;
    use UserHelper;

    /**
     * @return void
     */
    public function testListUsers(): void
    {
        $users = $this->createUsers($this->getFaker()->numberBetween(1, 5));

        $response = $this->doApiCall(
            $this->getRouteUrl(UsersController::ROUTE_NAME_LIST),
            Request::METHOD_GET,
            [],
            $this->createAuthCookie($users->first())
        );

        $this->assertResponseOk();
        $this->assertNotEmpty($this->getCookieValue($response, JWTServiceInterface::AUTHORIZATION_BEARER));

        $responseData = $response->getData(true);

        $this->assertArrayHasKey(UsersController::RESPONSE_PARAMETER_USERS, $responseData);
        $this->assertEquals($users->toArray(), $responseData[UsersController::RESPONSE_PARAMETER_USERS]);
    }

    /**
     * @return void
     */
    public function testUserDetails(): void
    {
        $user = $this->createUsers()->first();

        $response = $this->doApiCall(
            $this->getRouteUrl(
                UsersController::ROUTE_NAME_DETAILS,
                [
                    'userId' => $user->getId(),
                ]
            ),
            Request::METHOD_GET,
            [],
            $this->createAuthCookie($user)
        );

        $this->assertResponseOk();
        $this->assertNotEmpty($this->getCookieValue($response, JWTServiceInterface::AUTHORIZATION_BEARER));

        $responseData = $response->getData(true);

        $this->assertArrayHasKey(UsersController::RESPONSE_PARAMETER_USER, $responseData);
        $this->assertEquals($user->toArray(), $responseData[UsersController::RESPONSE_PARAMETER_USER]);
    }

    /**
     * @return void
     */
    public function testUserDetailsWithInvalidUserId(): void
    {
        $response = $this->doApiCall(
            $this->getRouteUrl(
                UsersController::ROUTE_NAME_DETAILS,
                [
                    'userId' => $this->getFaker()->numberBetween(),
                ]
            ),
            Request::METHOD_GET,
            [],
            $this->createAuthCookie($this->createUsers()->first())
        );

        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
        $this->assertNotEmpty($this->getCookieValue($response, JWTServiceInterface::AUTHORIZATION_BEARER));

        $responseData = $response->getData(true);

        $this->assertArrayNotHasKey(UsersController::RESPONSE_PARAMETER_USER, $responseData);
    }

    /**
     * @return void
     */
    public function testCreateUser(): void
    {
        $userData = [
            UserModelInterface::PROPERTY_EMAIL    => $this->getFaker()->safeEmail,
            UserModelInterface::PROPERTY_PASSWORD => $this->getFaker()->password(),
        ];

        $response = $this->doApiCall(
            $this->getRouteUrl(UsersController::ROUTE_NAME_CREATE),
            Request::METHOD_POST,
            $userData,
            $this->createAuthCookie($this->createUsers()->first())
        );

        $this->assertResponseStatus(Response::HTTP_CREATED);
        $this->assertNotEmpty($this->getCookieValue($response, JWTServiceInterface::AUTHORIZATION_BEARER));

        $responseData = $response->getData(true);

        $this->assertArrayHasKey(UsersController::RESPONSE_PARAMETER_USER, $responseData);
        $this->assertNotEmpty($responseData[UsersController::RESPONSE_PARAMETER_USER][UserModelInterface::PROPERTY_ID]);
        $this->assertEquals(
            $userData[UserModelInterface::PROPERTY_EMAIL],
            $responseData[UsersController::RESPONSE_PARAMETER_USER][UserModelInterface::PROPERTY_EMAIL]
        );
    }

    /**
     * @return void
     */
    public function testCreateUserWithoutRequiredData(): void
    {
        $response = $this->doApiCall(
            $this->getRouteUrl(UsersController::ROUTE_NAME_CREATE),
            Request::METHOD_POST,
            [],
            $this->createAuthCookie($this->createUsers()->first())
        );

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertNotEmpty($this->getCookieValue($response, JWTServiceInterface::AUTHORIZATION_BEARER));

        $responseData = $response->getData(true);

        $this->assertArrayHasKey(UserModelInterface::PROPERTY_EMAIL, $responseData);
        $this->assertEquals('validation.required', \reset($responseData[UserModelInterface::PROPERTY_EMAIL]));
        $this->assertArrayHasKey(UserModelInterface::PROPERTY_PASSWORD, $responseData);
        $this->assertEquals('validation.required', \reset($responseData[UserModelInterface::PROPERTY_PASSWORD]));
    }

    /**
     * @return void
     */
    public function testCreateUserWithInvalidEmail(): void
    {
        $response = $this->doApiCall(
            $this->getRouteUrl(UsersController::ROUTE_NAME_CREATE),
            Request::METHOD_POST,
            [
                UserModelInterface::PROPERTY_EMAIL => $this->getFaker()->word,
            ],
            $this->createAuthCookie($this->createUsers()->first())
        );

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertNotEmpty($this->getCookieValue($response, JWTServiceInterface::AUTHORIZATION_BEARER));

        $responseData = $response->getData(true);

        $this->assertArrayHasKey(UserModelInterface::PROPERTY_EMAIL, $responseData);
        $this->assertEquals('validation.email', \reset($responseData[UserModelInterface::PROPERTY_EMAIL]));
    }

    /**
     * @return void
     */
    public function testCreateUserWithDuplicatedEmail(): void
    {
        $user = $this->createUsers()->first();

        $response = $this->doApiCall(
            $this->getRouteUrl(UsersController::ROUTE_NAME_CREATE),
            Request::METHOD_POST,
            [
                UserModelInterface::PROPERTY_EMAIL => $user->getEmail(),
            ],
            $this->createAuthCookie($user)
        );

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertNotEmpty($this->getCookieValue($response, JWTServiceInterface::AUTHORIZATION_BEARER));

        $responseData = $response->getData(true);

        $this->assertArrayHasKey(UserModelInterface::PROPERTY_EMAIL, $responseData);
        $this->assertEquals('validation.unique', \reset($responseData[UserModelInterface::PROPERTY_EMAIL]));
    }

    /**
     * @return void
     */
    public function testEditUser(): void
    {
        $password = $this->getFaker()->password();

        $user = $this->createUsers()->first();
        $user
            ->setEmail($this->getFaker()->safeEmail)
            ->setPassword($password);

        $response = $this->doApiCall(
            $this->getRouteUrl(
                UsersController::ROUTE_NAME_EDIT,
                [
                    'userId' => $user->getId(),
                ]
            ),
            Request::METHOD_PUT,
            [
                UserModelInterface::PROPERTY_EMAIL    => $user->getEmail(),
                UserModelInterface::PROPERTY_PASSWORD => $password,
            ],
            $this->createAuthCookie($user)
        );

        $this->assertResponseOk();
        $this->assertNotEmpty($this->getCookieValue($response, JWTServiceInterface::AUTHORIZATION_BEARER));

        $responseData = $response->getData(true);

        $this->assertArrayHasKey(UsersController::RESPONSE_PARAMETER_USER, $responseData);
        $this->assertNotEmpty($responseData[UsersController::RESPONSE_PARAMETER_USER][UserModelInterface::PROPERTY_ID]);
        $this->assertEquals(
            $user->getEmail(),
            $responseData[UsersController::RESPONSE_PARAMETER_USER][UserModelInterface::PROPERTY_EMAIL]
        );
    }

    /**
     * @return void
     */
    public function testEditUserWithoutChanges(): void
    {
        $user = $this->createUsers()->first();

        $response = $this->doApiCall(
            $this->getRouteUrl(
                UsersController::ROUTE_NAME_EDIT,
                [
                    'userId' => $user->getId(),
                ]
            ),
            Request::METHOD_PUT,
            [],
            $this->createAuthCookie($user)
        );

        $this->assertResponseOk();
        $this->assertNotEmpty($this->getCookieValue($response, JWTServiceInterface::AUTHORIZATION_BEARER));

        $responseData = $response->getData(true);

        $this->assertArrayHasKey(UsersController::RESPONSE_PARAMETER_USER, $responseData);
        $this->assertNotEmpty($responseData[UsersController::RESPONSE_PARAMETER_USER][UserModelInterface::PROPERTY_ID]);
        $this->assertEquals(
            $user->getEmail(),
            $responseData[UsersController::RESPONSE_PARAMETER_USER][UserModelInterface::PROPERTY_EMAIL]
        );
    }

    /**
     * @return void
     */
    public function testEditUserWithInvalidUser(): void
    {
        $user = $this->createUsers()->first();

        $response = $this->doApiCall(
            $this->getRouteUrl(
                UsersController::ROUTE_NAME_EDIT,
                [
                    'userId' => $this->getFaker()->numberBetween(),
                ]
            ),
            Request::METHOD_PUT,
            [],
            $this->createAuthCookie($user)
        );

        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
        $this->assertNotEmpty($this->getCookieValue($response, JWTServiceInterface::AUTHORIZATION_BEARER));
    }

    /**
     * @return void
     */
    public function testEditUserWithEmptyData(): void
    {
        $user = $this->createUsers()->first();

        $response = $this->doApiCall(
            $this->getRouteUrl(
                UsersController::ROUTE_NAME_EDIT,
                [
                    'userId' => $user->getId(),
                ]
            ),
            Request::METHOD_PUT,
            [
                UserModelInterface::PROPERTY_EMAIL    => '',
                UserModelInterface::PROPERTY_PASSWORD => '',
            ],
            $this->createAuthCookie($user)
        );

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertNotEmpty($this->getCookieValue($response, JWTServiceInterface::AUTHORIZATION_BEARER));

        $responseData = $response->getData(true);

        $this->assertArrayHasKey(UserModelInterface::PROPERTY_EMAIL, $responseData);
        $this->assertEquals('validation.filled', \reset($responseData[UserModelInterface::PROPERTY_EMAIL]));
        $this->assertArrayHasKey(UserModelInterface::PROPERTY_PASSWORD, $responseData);
        $this->assertEquals('validation.filled', \reset($responseData[UserModelInterface::PROPERTY_PASSWORD]));
    }

    /**
     * @return void
     */
    public function testEditUserWithInvalidEmail(): void
    {
        $user = $this->createUsers()->first();

        $response = $this->doApiCall(
            $this->getRouteUrl(
                UsersController::ROUTE_NAME_EDIT,
                [
                    'userId' => $user->getId(),
                ]
            ),
            Request::METHOD_PUT,
            [
                UserModelInterface::PROPERTY_EMAIL => $this->getFaker()->word,
            ],
            $this->createAuthCookie($user)
        );

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertNotEmpty($this->getCookieValue($response, JWTServiceInterface::AUTHORIZATION_BEARER));

        $responseData = $response->getData(true);

        $this->assertArrayHasKey(UserModelInterface::PROPERTY_EMAIL, $responseData);
        $this->assertEquals('validation.email', \reset($responseData[UserModelInterface::PROPERTY_EMAIL]));
    }

    /**
     * @return void
     */
    public function testEditUserWithDuplicatedEmail(): void
    {
        $user = $this->createUsers()->first();

        $response = $this->doApiCall(
            $this->getRouteUrl(
                UsersController::ROUTE_NAME_EDIT,
                [
                    'userId' => $user->getId(),
                ]
            ),
            Request::METHOD_PUT,
            [
                UserModelInterface::PROPERTY_EMAIL => $this->createUsers()->first()->getEmail(),
            ],
            $this->createAuthCookie($user)
        );

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertNotEmpty($this->getCookieValue($response, JWTServiceInterface::AUTHORIZATION_BEARER));

        $responseData = $response->getData(true);

        $this->assertArrayHasKey(UserModelInterface::PROPERTY_EMAIL, $responseData);
        $this->assertEquals('validation.unique', \reset($responseData[UserModelInterface::PROPERTY_EMAIL]));
    }

    /**
     * @return void
     */
    public function testDeleteUser(): void
    {
        $userId = $this->createUsers()->first()->getId();

        $response = $this->doApiCall(
            $this->getRouteUrl(
                UsersController::ROUTE_NAME_DELETE,
                [
                    'userId' => $userId,
                ]
            ),
            Request::METHOD_DELETE,
            [],
            $this->createAuthCookie($this->createUsers()->first())
        );

        $this->assertResponseStatus(Response::HTTP_NO_CONTENT);
        $this->assertNotEmpty($this->getCookieValue($response, JWTServiceInterface::AUTHORIZATION_BEARER));

        $this->assertEmpty($this->getUserRepository()->find($userId));
    }

    /**
     * @return void
     */
    public function testDeleteWithInvalidUserId(): void
    {
        $response = $this->doApiCall(
            $this->getRouteUrl(
                UsersController::ROUTE_NAME_DELETE,
                [
                    'userId' => $this->getFaker()->numberBetween(2),
                ]
            ),
            Request::METHOD_DELETE,
            [],
            $this->createAuthCookie($this->createUsers()->first())
        );

        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
        $this->assertNotEmpty($this->getCookieValue($response, JWTServiceInterface::AUTHORIZATION_BEARER));
    }

    /**
     * @return void
     */
    public function testDeleteWithInvalidUser(): void
    {
        $user = $this->createUsers()->first();

        $response = $this->doApiCall(
            $this->getRouteUrl(
                UsersController::ROUTE_NAME_DELETE,
                [
                    'userId' => $user->getId(),
                ]
            ),
            Request::METHOD_DELETE,
            [],
            $this->createAuthCookie($user)
        );

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
        $this->assertNotEmpty($this->getCookieValue($response, JWTServiceInterface::AUTHORIZATION_BEARER));
    }
}