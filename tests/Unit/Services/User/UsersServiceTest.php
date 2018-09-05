<?php
use App\Exceptions\InvalidParameterException;
use App\Exceptions\ModelNotFoundException;
use App\Models\User\UserModelFactoryInterface;
use App\Models\User\UserModelInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\User\UsersService;
use App\Services\User\UsersServiceInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use LaravelDoctrine\Migrations\Testing\DatabaseMigrations;
use Test\ModelHelper;
use Test\UserHelper;

/**
 * Class UserServiceTest
 */
class UsersServiceTest extends TestCase
{

    use DatabaseMigrations;
    use ModelHelper;
    use UserHelper;

    //region Tests

    /**
     * @return void
     *
     * @throws ModelNotFoundException
     */
    public function testGetUser(): void
    {
        $user = $this->createUsers()->first();

        $this->assertEquals($user, $this->createUserService()->getUser($user->getId()));
    }

    /**
     * @return void
     */
    public function testGetUserWithInvalidUserId(): void
    {
        try {
            $this->createUserService()->getUser($this->getFaker()->numberBetween());

            $this->assertTrue(false);
        } catch (ModelNotFoundException $e) {
            $this->assertEquals(UserModelInterface::class, $e->getModelClass());
        }
    }

    /**
     * @return void
     */
    public function testListUsers(): void
    {
        $users = $this->createUsers($this->getFaker()->numberBetween(2, 5));

        $this->assertEquals($users, $this->createUserService()->listUsers());
    }

    /**
     * @return void
     */
    public function testListUsersWithEmptyList(): void
    {
        $this->assertEquals(new Collection(), $this->createUserService()->listUsers());
    }

    /**
     * @return void
     */
    public function testCreateUser(): void
    {
        $userData = [
            UserModelInterface::PROPERTY_EMAIL => $this->getFaker()->safeEmail,
            UserModelInterface::PROPERTY_PASSWORD => $this->getFaker()->password,
        ];

        $user = $this->createUserService()->createUser($userData);

        $this->assertEquals($userData[UserModelInterface::PROPERTY_EMAIL], $user->getEmail());
        $this->assertTrue(Hash::check($userData[UserModelInterface::PROPERTY_PASSWORD], $user->getAuthPassword()));
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidData(): void
    {
        //missing email
        try {
            $this->createUserService()->createUser([
                UserModelInterface::PROPERTY_PASSWORD => $this->getFaker()->password,
            ]);

            $this->assertTrue(false);
        } catch (InvalidParameterException $e) {
            $this->assertTrue(true);
        }

        //empty email
        try {
            $this->createUserService()->createUser([
                UserModelInterface::PROPERTY_EMAIL    => '',
                UserModelInterface::PROPERTY_PASSWORD => $this->getFaker()->password,
            ]);

            $this->assertTrue(false);
        } catch (InvalidParameterException $e) {
            $this->assertTrue(true);
        }

        //missing password
        try {
            $this->createUserService()->createUser([
                UserModelInterface::PROPERTY_EMAIL    => $this->getFaker()->safeEmail,
            ]);

            $this->assertTrue(false);
        } catch (InvalidParameterException $e) {
            $this->assertTrue(true);
        }

        //empty password
        try {
            $this->createUserService()->createUser([
                UserModelInterface::PROPERTY_EMAIL    => $this->getFaker()->safeEmail,
                UserModelInterface::PROPERTY_PASSWORD => '',
            ]);

            $this->assertTrue(false);
        } catch (InvalidParameterException $e) {
            $this->assertTrue(true);
        }

        //duplicated email
        try {
            $this->createUserService()->createUser([
                UserModelInterface::PROPERTY_EMAIL    => $this->createUsers()->first()->getEmail(),
                UserModelInterface::PROPERTY_PASSWORD => $this->getFaker()->password,
            ]);

            $this->assertTrue(false);
        } catch (InvalidParameterException $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * @return void
     */
    public function testEditUser(): void
    {
        $userData = [
            UserModelInterface::PROPERTY_EMAIL => $this->getFaker()->safeEmail,
            UserModelInterface::PROPERTY_PASSWORD => $this->getFaker()->password,
        ];

        $user = $this->createUserService()->editUser($this->createUsers()->first()->getId(), $userData);

        $this->assertEquals($userData[UserModelInterface::PROPERTY_EMAIL], $user->getEmail());
        $this->assertTrue(Hash::check($userData[UserModelInterface::PROPERTY_PASSWORD], $user->getAuthPassword()));
    }

    /**
     * @return void
     */
    public function testEditUserWithoutChanges(): void
    {
        $user = $this->createUsers()->first();

        $this->assertEquals($user, $this->createUserService()->editUser($user->getId(), []));
    }

    /**
     * @return void
     */
    public function testEditUserWithInvalidUserId(): void
    {
        try {
            $this->createUserService()->editUser($this->getFaker()->numberBetween(), []);

            $this->assertTrue(false);
        } catch (ModelNotFoundException $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * @return void
     */
    public function testEditUserWithInvalidData(): void
    {
        $user = $this->createUsers()->first();

        //empty email
        try {
            $this->createUserService()->editUser(
                $user->getId(),
                [
                    UserModelInterface::PROPERTY_EMAIL    => '',
                    UserModelInterface::PROPERTY_PASSWORD => $this->getFaker()->password,
                ]
            );

            $this->assertTrue(false);
        } catch (InvalidParameterException $e) {
            $this->assertTrue(true);
        }

        //empty password
        try {
            $this->createUserService()->editUser(
                $user->getId(),
                [
                    UserModelInterface::PROPERTY_EMAIL    => $this->getFaker()->safeEmail,
                    UserModelInterface::PROPERTY_PASSWORD => '',
                ]
            );

            $this->assertTrue(false);
        } catch (InvalidParameterException $e) {
            $this->assertTrue(true);
        }

        //duplicated email
        try {
            $this->createUserService()->editUser(
                $user->getId(),
                [
                    UserModelInterface::PROPERTY_EMAIL    => $this->createUsers()->first()->getEmail(),
                    UserModelInterface::PROPERTY_PASSWORD => $this->getFaker()->password,
                ]
            );

            $this->assertTrue(false);
        } catch (InvalidParameterException $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * @return void
     */
    public function testDeleteUser(): void
    {
        $userId = $this->createUsers()->first()->getId();

        $this->createUserService()->deleteUser($userId);

        $this->assertEmpty($this->getUserRepository()->find($userId));
    }

    /**
     * @return void
     */
    public function testDeleteUserWithInvalidUserId(): void
    {
        try {
            $this->createUserService()->deleteUser($this->getFaker()->numberBetween());

            $this->assertTrue(false);
        } catch (ModelNotFoundException $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * @return void
     */
    public function testValidateUserCredentials(): void
    {
        $password = $this->getFaker()->password();

        $user = $this->createUsers(1, [UserModelInterface::PROPERTY_PASSWORD => Hash::make($password)])->first();

        $this->assertEquals($user, $this->createUserService()->validateUserCredentials($user->getEmail(), $password));
    }

    /**
     * @return void
     */
    public function testValidateUserCredentialsWithInvalidEmail(): void
    {
        try {
            $this->createUserService()->validateUserCredentials(
                $this->getFaker()->safeEmail,
                $this->getFaker()->password()
            );

            $this->assertTrue(false);
        } catch (AuthorizationException $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * @return void
     */
    public function testValidateUserCredentialsWithInvalidPassword(): void
    {
        try {
            $this->createUserService()->validateUserCredentials(
                $this->createUsers()->first()->getEmail(),
                $this->getFaker()->password()
            );

            $this->assertTrue(false);
        } catch (AuthorizationException $e) {
            $this->assertTrue(true);
        }
    }

    //endregion

    /**
     * @param UserRepositoryInterface|null   $userRepository
     * @param UserModelFactoryInterface|null $userModelFactory
     *
     * @return UsersServiceInterface
     */
    private function createUserService(
        UserRepositoryInterface $userRepository = null,
        UserModelFactoryInterface $userModelFactory = null
    ): UsersServiceInterface
    {
        return new UsersService(
            $userRepository ?: $this->getUserRepository(),
            $userModelFactory ?: $this->getUserModelFactory()
        );
    }
}