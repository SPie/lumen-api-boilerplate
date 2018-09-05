<?php

use App\Exceptions\InvalidParameterException;
use App\Models\User\UserModelFactoryInterface;
use App\Models\User\UserModelInterface;
use Illuminate\Support\Facades\Hash;
use LaravelDoctrine\Migrations\Testing\DatabaseMigrations;
use Test\AuthHelper;
use Test\ModelHelper;
use Test\UserHelper;

/**
 * Class UserDoctrineModelFactoryTest
 */
class UserDoctrineModelFactoryTest extends TestCase
{

    use AuthHelper;
    use DatabaseMigrations;
    use ModelHelper;
    use UserHelper;

    //region Tests

    /**
     * @return void
     */
    public function testCreate(): void
    {
        $data = [
            UserModelInterface::PROPERTY_EMAIL                => $this->getFaker()->safeEmail,
            UserModelInterface::PROPERTY_PASSWORD             => $this->getFaker()->password(),
            UserModelInterface::PROPERTY_ID                   => $this->getFaker()->numberBetween(),
            UserModelInterface::PROPERTY_CREATED_AT           => $this->getFaker()->dateTime(),
            UserModelInterface::PROPERTY_UPDATED_AT           => $this->getFaker()->dateTime(),
            UserModelInterface::PROPERTY_DELETED_AT           => $this->getFaker()->dateTime(),
            UserModelInterface::PROPERTY_LOGIN_REFRESH_TOKENS => $this->createLoginRefreshTokens()->all(),
        ];

        /** @var UserModelInterface $user */
        $user = $this->getUserModelFactory()->create($data);

        $this->assertEquals($data[UserModelInterface::PROPERTY_EMAIL], $user->getEmail());
        $this->assertTrue(Hash::check($data[UserModelInterface::PROPERTY_PASSWORD], $user->getAuthPassword()));
        $this->assertEquals($data[UserModelInterface::PROPERTY_ID], $user->getId());
        $this->assertEquals($data[UserModelInterface::PROPERTY_CREATED_AT], $user->getCreatedAt());
        $this->assertEquals($data[UserModelInterface::PROPERTY_UPDATED_AT], $user->getUpdatedAt());
        $this->assertEquals($data[UserModelInterface::PROPERTY_DELETED_AT], $user->getDeletedAt());
        $this->assertEquals(
            $data[UserModelInterface::PROPERTY_LOGIN_REFRESH_TOKENS],
            $user->getLoginRefreshTokens()->all()
        );
    }

    /**
     * @return void
     */
    public function testCreateOnlyWithRequiredParameters(): void
    {
        $data = [
            UserModelInterface::PROPERTY_EMAIL    => $this->getFaker()->safeEmail,
            UserModelInterface::PROPERTY_PASSWORD => $this->getFaker()->password(),
        ];

        /** @var UserModelInterface $user */
        $user = $this->getUserModelFactory()->create($data);

        $this->assertEquals($data[UserModelInterface::PROPERTY_EMAIL], $user->getEmail());
        $this->assertTrue(Hash::check($data[UserModelInterface::PROPERTY_PASSWORD], $user->getAuthPassword()));
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidParameters(): void
    {
        $loginRefreshTokens = $this->createLoginRefreshTokens()->all();

        //no email
        try {
            $this->getUserModelFactory()->create([
                UserModelInterface::PROPERTY_PASSWORD             => $this->getFaker()->password(),
                UserModelInterface::PROPERTY_ID                   => $this->getFaker()->numberBetween(),
                UserModelInterface::PROPERTY_CREATED_AT           => $this->getFaker()->dateTime(),
                UserModelInterface::PROPERTY_UPDATED_AT           => $this->getFaker()->dateTime(),
                UserModelInterface::PROPERTY_DELETED_AT           => $this->getFaker()->dateTime(),
                UserModelInterface::PROPERTY_LOGIN_REFRESH_TOKENS => $loginRefreshTokens,
            ]);

            $this->assertTrue(false);
        } catch (InvalidParameterException $e) {
            $this->assertTrue(true);
        }

        //empty email
        try {
            $this->getUserModelFactory()->create([
                UserModelInterface::PROPERTY_EMAIL                => '',
                UserModelInterface::PROPERTY_PASSWORD             => $this->getFaker()->password(),
                UserModelInterface::PROPERTY_ID                   => $this->getFaker()->numberBetween(),
                UserModelInterface::PROPERTY_CREATED_AT           => $this->getFaker()->dateTime(),
                UserModelInterface::PROPERTY_UPDATED_AT           => $this->getFaker()->dateTime(),
                UserModelInterface::PROPERTY_DELETED_AT           => $this->getFaker()->dateTime(),
                UserModelInterface::PROPERTY_LOGIN_REFRESH_TOKENS => $loginRefreshTokens,
            ]);

            $this->assertTrue(false);
        } catch (InvalidParameterException $e) {
            $this->assertTrue(true);
        }

        //empty password
        try {
            $this->getUserModelFactory()->create([
                UserModelInterface::PROPERTY_EMAIL                => $this->getFaker()->safeEmail,
                UserModelInterface::PROPERTY_PASSWORD             => '',
                UserModelInterface::PROPERTY_ID                   => $this->getFaker()->numberBetween(),
                UserModelInterface::PROPERTY_CREATED_AT           => $this->getFaker()->dateTime(),
                UserModelInterface::PROPERTY_UPDATED_AT           => $this->getFaker()->dateTime(),
                UserModelInterface::PROPERTY_DELETED_AT           => $this->getFaker()->dateTime(),
                UserModelInterface::PROPERTY_LOGIN_REFRESH_TOKENS => $loginRefreshTokens,
            ]);

            $this->assertTrue(false);
        } catch (InvalidParameterException $e) {
            $this->assertTrue(true);
        }

        //invalid id
        try {
            $this->getUserModelFactory()->create([
                UserModelInterface::PROPERTY_EMAIL                => $this->getFaker()->safeEmail,
                UserModelInterface::PROPERTY_PASSWORD             => $this->getFaker()->password(),
                UserModelInterface::PROPERTY_ID                   => $this->getFaker()->word,
                UserModelInterface::PROPERTY_CREATED_AT           => $this->getFaker()->dateTime(),
                UserModelInterface::PROPERTY_UPDATED_AT           => $this->getFaker()->dateTime(),
                UserModelInterface::PROPERTY_DELETED_AT           => $this->getFaker()->dateTime(),
                UserModelInterface::PROPERTY_LOGIN_REFRESH_TOKENS => $loginRefreshTokens,
            ]);

            $this->assertTrue(false);
        } catch (InvalidParameterException $e) {
            $this->assertTrue(true);
        }

        //invalid created at
        try {
            $this->getUserModelFactory()->create([
                UserModelInterface::PROPERTY_EMAIL                => $this->getFaker()->safeEmail,
                UserModelInterface::PROPERTY_PASSWORD             => $this->getFaker()->password(),
                UserModelInterface::PROPERTY_ID                   => $this->getFaker()->numberBetween(),
                UserModelInterface::PROPERTY_CREATED_AT           => $this->getFaker()->word,
                UserModelInterface::PROPERTY_UPDATED_AT           => $this->getFaker()->dateTime(),
                UserModelInterface::PROPERTY_DELETED_AT           => $this->getFaker()->dateTime(),
                UserModelInterface::PROPERTY_LOGIN_REFRESH_TOKENS => $loginRefreshTokens,
            ]);

            $this->assertTrue(false);
        } catch (InvalidParameterException $e) {
            $this->assertTrue(true);
        }

        //invalid updated at
        try {
            $this->getUserModelFactory()->create([
                UserModelInterface::PROPERTY_EMAIL                => $this->getFaker()->safeEmail,
                UserModelInterface::PROPERTY_PASSWORD             => $this->getFaker()->password(),
                UserModelInterface::PROPERTY_ID                   => $this->getFaker()->numberBetween(),
                UserModelInterface::PROPERTY_CREATED_AT           => $this->getFaker()->dateTime(),
                UserModelInterface::PROPERTY_UPDATED_AT           => $this->getFaker()->word,
                UserModelInterface::PROPERTY_DELETED_AT           => $this->getFaker()->dateTime(),
                UserModelInterface::PROPERTY_LOGIN_REFRESH_TOKENS => $loginRefreshTokens,
            ]);

            $this->assertTrue(false);
        } catch (InvalidParameterException $e) {
            $this->assertTrue(true);
        }

        //invalid deleted at
        try {
            $this->getUserModelFactory()->create([
                UserModelInterface::PROPERTY_EMAIL                => $this->getFaker()->safeEmail,
                UserModelInterface::PROPERTY_PASSWORD             => $this->getFaker()->password(),
                UserModelInterface::PROPERTY_ID                   => $this->getFaker()->numberBetween(),
                UserModelInterface::PROPERTY_CREATED_AT           => $this->getFaker()->dateTime(),
                UserModelInterface::PROPERTY_UPDATED_AT           => $this->getFaker()->dateTime(),
                UserModelInterface::PROPERTY_DELETED_AT           => $this->getFaker()->word,
                UserModelInterface::PROPERTY_LOGIN_REFRESH_TOKENS => $loginRefreshTokens,
            ]);

            $this->assertTrue(false);
        } catch (InvalidParameterException $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * @return void
     */
    public function testFill(): void
    {
        $data = [
            UserModelInterface::PROPERTY_EMAIL                => $this->getFaker()->safeEmail,
            UserModelInterface::PROPERTY_PASSWORD             => $this->getFaker()->password(),
            UserModelInterface::PROPERTY_ID                   => $this->getFaker()->numberBetween(),
            UserModelInterface::PROPERTY_CREATED_AT           => $this->getFaker()->dateTime(),
            UserModelInterface::PROPERTY_UPDATED_AT           => $this->getFaker()->dateTime(),
            UserModelInterface::PROPERTY_DELETED_AT           => $this->getFaker()->dateTime(),
            UserModelInterface::PROPERTY_LOGIN_REFRESH_TOKENS => $this->createLoginRefreshTokens()->all(),
        ];

        /** @var UserModelInterface $user */
        $user = $this->getUserModelFactory()->fill($this->createUsers()->first(), $data);

        $this->assertEquals($data[UserModelInterface::PROPERTY_EMAIL], $user->getEmail());
        $this->assertTrue(Hash::check($data[UserModelInterface::PROPERTY_PASSWORD], $user->getAuthPassword()));
        $this->assertEquals($data[UserModelInterface::PROPERTY_ID], $user->getId());
        $this->assertEquals($data[UserModelInterface::PROPERTY_CREATED_AT], $user->getCreatedAt());
        $this->assertEquals($data[UserModelInterface::PROPERTY_UPDATED_AT], $user->getUpdatedAt());
        $this->assertEquals($data[UserModelInterface::PROPERTY_DELETED_AT], $user->getDeletedAt());
        $this->assertEquals(
            $data[UserModelInterface::PROPERTY_LOGIN_REFRESH_TOKENS],
            $user->getLoginRefreshTokens()->all()
        );
    }

    //endregion

    /**
     * @return UserModelFactoryInterface
     */
    private function getUserModelFactory(): UserModelFactoryInterface
    {
        return $this->app->get(UserModelFactoryInterface::class);
    }

}