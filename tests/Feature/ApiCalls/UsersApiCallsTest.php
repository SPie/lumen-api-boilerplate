<?php
use App\Http\Controllers\User\UsersController;
use App\Models\User\UserModelInterface;
use App\Services\JWT\TokenProviderInterface;
use App\Services\JWT\JWTServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Test\ApiHelper;
use Test\DatabaseMigrations;
use Test\ModelHelper;
use Test\ResponseHelper;
use Test\UserHelper;

/**
 * Class UsersApiCallsTest
 */
class UsersApiCallsTest extends TestCase
{

    use ApiHelper;
    use DatabaseMigrations;
    use ModelHelper;
    use ResponseHelper;
    use UserHelper;

    const BEARER_AUTHORIZATION = 'Authorization';

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testListUsers(): void
    {
        $users = $this->createUsers($this->getFaker()->numberBetween(1, 5));

        $response = $this->doApiCall(
            $this->getRouteUrl(UsersController::ROUTE_NAME_LIST),
            Request::METHOD_GET,
            [],
            null,
            $this->createAuthHeader($users->first())
        );

        $this->assertResponseOk();
        $this->assertNotEmpty($this->getHeaderValue($response, self::BEARER_AUTHORIZATION));

        $responseData = $response->getData(true);

        $this->assertArrayHasKey(UsersController::RESPONSE_PARAMETER_USERS, $responseData);
        $this->assertEquals($users->toArray(), $responseData[UsersController::RESPONSE_PARAMETER_USERS]);
    }

    /**
     * @return void
     *
     * @throws Exception
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
            null,
            $this->createAuthHeader($user)
        );

        $this->assertResponseOk();
        $this->assertNotEmpty($this->getHeaderValue($response, self::BEARER_AUTHORIZATION));

        $responseData = $response->getData(true);

        $this->assertArrayHasKey(UsersController::RESPONSE_PARAMETER_USER, $responseData);
        $this->assertEquals($user->toArray(), $responseData[UsersController::RESPONSE_PARAMETER_USER]);
    }

    /**
     * @return void
     *
     * @throws Exception
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
            null,
            $this->createAuthHeader($this->createUsers()->first())
        );

        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
        $this->assertNotEmpty($this->getHeaderValue($response, self::BEARER_AUTHORIZATION));

        $responseData = $response->getData(true);

        $this->assertArrayNotHasKey(UsersController::RESPONSE_PARAMETER_USER, $responseData);
    }

    /**
     * @return void
     *
     * @throws Exception
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
            null,
            $this->createAuthHeader($this->createUsers()->first())
        );

        $this->assertResponseStatus(Response::HTTP_CREATED);
        $this->assertNotEmpty($this->getHeaderValue($response, self::BEARER_AUTHORIZATION));

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
     *
     * @throws Exception
     */
    public function testCreateUserWithoutRequiredData(): void
    {
        $response = $this->doApiCall(
            $this->getRouteUrl(UsersController::ROUTE_NAME_CREATE),
            Request::METHOD_POST,
            [],
            null,
            $this->createAuthHeader($this->createUsers()->first())
        );

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertNotEmpty($this->getHeaderValue($response, self::BEARER_AUTHORIZATION));

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
    public function testCreateUserWithInvalidEmail(): void
    {
        $response = $this->doApiCall(
            $this->getRouteUrl(UsersController::ROUTE_NAME_CREATE),
            Request::METHOD_POST,
            [
                UserModelInterface::PROPERTY_EMAIL => $this->getFaker()->word,
            ],
            null,
            $this->createAuthHeader($this->createUsers()->first())
        );

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertNotEmpty($this->getHeaderValue($response, self::BEARER_AUTHORIZATION));

        $responseData = $response->getData(true);

        $this->assertArrayHasKey(UserModelInterface::PROPERTY_EMAIL, $responseData);
        $this->assertEquals('validation.email', \reset($responseData[UserModelInterface::PROPERTY_EMAIL]));
    }

    /**
     * @return void
     *
     * @throws Exception
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
            null,
            $this->createAuthHeader($user)
        );

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertNotEmpty($this->getHeaderValue($response, self::BEARER_AUTHORIZATION));

        $responseData = $response->getData(true);

        $this->assertArrayHasKey(UserModelInterface::PROPERTY_EMAIL, $responseData);
        $this->assertEquals('validation.unique', \reset($responseData[UserModelInterface::PROPERTY_EMAIL]));
    }

    /**
     * @return void
     *
     * @throws Exception
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
            null,
            $this->createAuthHeader($user)
        );

        $this->assertResponseOk();
        $this->assertNotEmpty($this->getHeaderValue($response, self::BEARER_AUTHORIZATION));

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
     *
     * @throws Exception
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
            null,
            $this->createAuthHeader($user)
        );

        $this->assertResponseOk();
        $this->assertNotEmpty($this->getHeaderValue($response, self::BEARER_AUTHORIZATION));

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
     *
     * @throws Exception
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
            null,
            $this->createAuthHeader($user)
        );

        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
        $this->assertNotEmpty($this->getHeaderValue($response, self::BEARER_AUTHORIZATION));
    }

    /**
     * @return void
     *
     * @throws Exception
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
            null,
            $this->createAuthHeader($user)
        );

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertNotEmpty($this->getHeaderValue($response, self::BEARER_AUTHORIZATION));

        $responseData = $response->getData(true);

        $this->assertArrayHasKey(UserModelInterface::PROPERTY_EMAIL, $responseData);
        $this->assertEquals('validation.filled', \reset($responseData[UserModelInterface::PROPERTY_EMAIL]));
        $this->assertArrayHasKey(UserModelInterface::PROPERTY_PASSWORD, $responseData);
        $this->assertEquals('validation.filled', \reset($responseData[UserModelInterface::PROPERTY_PASSWORD]));
    }

    /**
     * @return void
     *
     * @throws Exception
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
            null,
            $this->createAuthHeader($user)
        );

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertNotEmpty($this->getHeaderValue($response, self::BEARER_AUTHORIZATION));

        $responseData = $response->getData(true);

        $this->assertArrayHasKey(UserModelInterface::PROPERTY_EMAIL, $responseData);
        $this->assertEquals('validation.email', \reset($responseData[UserModelInterface::PROPERTY_EMAIL]));
    }

    /**
     * @return void
     *
     * @throws Exception
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
            null,
            $this->createAuthHeader($user)
        );

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertNotEmpty($this->getHeaderValue($response, self::BEARER_AUTHORIZATION));

        $responseData = $response->getData(true);

        $this->assertArrayHasKey(UserModelInterface::PROPERTY_EMAIL, $responseData);
        $this->assertEquals('validation.unique', \reset($responseData[UserModelInterface::PROPERTY_EMAIL]));
    }

    /**
     * @return void
     *
     * @throws Exception
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
            null,
            $this->createAuthHeader($this->createUsers()->first())
        );

        $this->assertResponseStatus(Response::HTTP_NO_CONTENT);
        $this->assertNotEmpty($this->getHeaderValue($response, self::BEARER_AUTHORIZATION));

        $this->assertEmpty($this->getUserRepository()->find($userId));
    }

    /**
     * @return void
     *
     * @throws Exception
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
            null,
            $this->createAuthHeader($this->createUsers()->first())
        );

        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
        $this->assertNotEmpty($this->getHeaderValue($response, self::BEARER_AUTHORIZATION));
    }

    /**
     * @return void
     *
     * @throws Exception
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
            null,
            $this->createAuthHeader($user)
        );

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
        $this->assertNotEmpty($this->getHeaderValue($response, self::BEARER_AUTHORIZATION));
    }
}