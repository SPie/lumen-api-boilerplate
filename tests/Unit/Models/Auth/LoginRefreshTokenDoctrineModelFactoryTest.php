<?php

use App\Exceptions\InvalidParameterException;
use App\Models\Auth\LoginRefreshTokenModelFactoryInterface;
use App\Models\Auth\LoginRefreshTokenModelInterface;
use LaravelDoctrine\Migrations\Testing\DatabaseMigrations;
use Test\AuthHelper;
use Test\ModelHelper;
use Test\UserHelper;

/**
 * Class LoginRefreshTokenDoctrineModelFactory
 */
class LoginRefreshTokenDoctrineModelFactoryTest extends TestCase
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
            LoginRefreshTokenModelInterface::PROPERTY_TOKEN       => $this->getFaker()->uuid,
            LoginRefreshTokenModelInterface::PROPERTY_DISABLED_AT => $this->getFaker()->dateTime,
            LoginRefreshTokenModelInterface::PROPERTY_USER        => $this->createUsers()->first(),
            LoginRefreshTokenModelInterface::PROPERTY_ID          => $this->getFaker()->numberBetween(),
            LoginRefreshTokenModelInterface::PROPERTY_CREATED_AT  => $this->getFaker()->dateTime,
            LoginRefreshTokenModelInterface::PROPERTY_UPDATED_AT  => $this->getFaker()->dateTime,
        ];

        $loginRefreshToken = $this->getLoginRefreshTokenModelFactory()->create($data);

        $this->assertEquals(
            $data[LoginRefreshTokenModelInterface::PROPERTY_TOKEN],
            $loginRefreshToken->getToken()
        );
        $this->assertEquals(
            $data[LoginRefreshTokenModelInterface::PROPERTY_DISABLED_AT],
            $loginRefreshToken->getDisabledAt()
        );
        $this->assertEquals(
            $data[LoginRefreshTokenModelInterface::PROPERTY_USER],
            $loginRefreshToken->getUser()
        );
        $this->assertEquals(
            $data[LoginRefreshTokenModelInterface::PROPERTY_ID],
            $loginRefreshToken->getId()
        );
        $this->assertEquals(
            $data[LoginRefreshTokenModelInterface::PROPERTY_CREATED_AT],
            $loginRefreshToken->getCreatedAt()
        );
        $this->assertEquals(
            $data[LoginRefreshTokenModelInterface::PROPERTY_UPDATED_AT],
            $loginRefreshToken->getUpdatedAt()
        );
    }

    /**
     * @return void
     */
    public function testCreateOnlyWithRequiredParameters(): void
    {
        $data = [
            LoginRefreshTokenModelInterface::PROPERTY_TOKEN       => $this->getFaker()->uuid,
        ];

        $loginRefreshToken = $this->getLoginRefreshTokenModelFactory()->create($data);

        $this->assertEquals(
            $data[LoginRefreshTokenModelInterface::PROPERTY_TOKEN],
            $loginRefreshToken->getToken()
        );
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidParameters(): void
    {
        $user = $this->createUsers()->first();

        //missing token
        try {
            $this->getLoginRefreshTokenModelFactory()->create([
                LoginRefreshTokenModelInterface::PROPERTY_DISABLED_AT => $this->getFaker()->dateTime,
                LoginRefreshTokenModelInterface::PROPERTY_USER        => $user,
                LoginRefreshTokenModelInterface::PROPERTY_ID          => $this->getFaker()->numberBetween(),
                LoginRefreshTokenModelInterface::PROPERTY_CREATED_AT  => $this->getFaker()->dateTime,
                LoginRefreshTokenModelInterface::PROPERTY_UPDATED_AT  => $this->getFaker()->dateTime,
            ]);

            $this->assertTrue(false);
        } catch (InvalidParameterException $e) {
            $this->assertTrue(true);
        }

        //invalid disabled at
        try {
            $this->getLoginRefreshTokenModelFactory()->create([
                LoginRefreshTokenModelInterface::PROPERTY_TOKEN       => $this->getFaker()->uuid,
                LoginRefreshTokenModelInterface::PROPERTY_DISABLED_AT => $this->getFaker()->word,
                LoginRefreshTokenModelInterface::PROPERTY_USER        => $user,
                LoginRefreshTokenModelInterface::PROPERTY_ID          => $this->getFaker()->numberBetween(),
                LoginRefreshTokenModelInterface::PROPERTY_CREATED_AT  => $this->getFaker()->dateTime,
                LoginRefreshTokenModelInterface::PROPERTY_UPDATED_AT  => $this->getFaker()->dateTime,
            ]);

            $this->assertTrue(false);
        } catch (InvalidParameterException $e) {
            $this->assertTrue(true);
        }

        //invalid user
        try {
            $this->getLoginRefreshTokenModelFactory()->create([
                LoginRefreshTokenModelInterface::PROPERTY_TOKEN       => $this->getFaker()->uuid,
                LoginRefreshTokenModelInterface::PROPERTY_DISABLED_AT => $this->getFaker()->dateTime,
                LoginRefreshTokenModelInterface::PROPERTY_USER        => $this->getFaker()->word,
                LoginRefreshTokenModelInterface::PROPERTY_ID          => $this->getFaker()->numberBetween(),
                LoginRefreshTokenModelInterface::PROPERTY_CREATED_AT  => $this->getFaker()->dateTime,
                LoginRefreshTokenModelInterface::PROPERTY_UPDATED_AT  => $this->getFaker()->dateTime,
            ]);

            $this->assertTrue(false);
        } catch (InvalidParameterException $e) {
            $this->assertTrue(true);
        }

        //invalid id
        try {
            $this->getLoginRefreshTokenModelFactory()->create([
                LoginRefreshTokenModelInterface::PROPERTY_TOKEN       => $this->getFaker()->uuid,
                LoginRefreshTokenModelInterface::PROPERTY_DISABLED_AT => $this->getFaker()->dateTime,
                LoginRefreshTokenModelInterface::PROPERTY_USER        => $user,
                LoginRefreshTokenModelInterface::PROPERTY_ID          => $this->getFaker()->word,
                LoginRefreshTokenModelInterface::PROPERTY_CREATED_AT  => $this->getFaker()->dateTime,
                LoginRefreshTokenModelInterface::PROPERTY_UPDATED_AT  => $this->getFaker()->dateTime,
            ]);

            $this->assertTrue(false);
        } catch (InvalidParameterException $e) {
            $this->assertTrue(true);
        }

        //invalid created at
        try {
            $this->getLoginRefreshTokenModelFactory()->create([
                LoginRefreshTokenModelInterface::PROPERTY_TOKEN       => $this->getFaker()->uuid,
                LoginRefreshTokenModelInterface::PROPERTY_DISABLED_AT => $this->getFaker()->dateTime,
                LoginRefreshTokenModelInterface::PROPERTY_USER        => $user,
                LoginRefreshTokenModelInterface::PROPERTY_ID          => $this->getFaker()->numberBetween(),
                LoginRefreshTokenModelInterface::PROPERTY_CREATED_AT  => $this->getFaker()->word,
                LoginRefreshTokenModelInterface::PROPERTY_UPDATED_AT  => $this->getFaker()->dateTime,
            ]);

            $this->assertTrue(false);
        } catch (InvalidParameterException $e) {
            $this->assertTrue(true);
        }

        //invalid updated at
        try {
            $this->getLoginRefreshTokenModelFactory()->create([
                LoginRefreshTokenModelInterface::PROPERTY_TOKEN       => $this->getFaker()->uuid,
                LoginRefreshTokenModelInterface::PROPERTY_DISABLED_AT => $this->getFaker()->dateTime,
                LoginRefreshTokenModelInterface::PROPERTY_USER        => $user,
                LoginRefreshTokenModelInterface::PROPERTY_ID          => $this->getFaker()->numberBetween(),
                LoginRefreshTokenModelInterface::PROPERTY_CREATED_AT  => $this->getFaker()->dateTime,
                LoginRefreshTokenModelInterface::PROPERTY_UPDATED_AT  => $this->getFaker()->word,
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
            LoginRefreshTokenModelInterface::PROPERTY_TOKEN       => $this->getFaker()->uuid,
            LoginRefreshTokenModelInterface::PROPERTY_DISABLED_AT => $this->getFaker()->dateTime,
            LoginRefreshTokenModelInterface::PROPERTY_USER        => $this->createUsers()->first(),
            LoginRefreshTokenModelInterface::PROPERTY_ID          => $this->getFaker()->numberBetween(),
            LoginRefreshTokenModelInterface::PROPERTY_CREATED_AT  => $this->getFaker()->dateTime,
            LoginRefreshTokenModelInterface::PROPERTY_UPDATED_AT  => $this->getFaker()->dateTime,
        ];

        $loginRefreshToken = $this->getLoginRefreshTokenModelFactory()->fill(
            $this->createLoginRefreshTokens()->first(),
            $data
        );

        $this->assertEquals(
            $data[LoginRefreshTokenModelInterface::PROPERTY_TOKEN],
            $loginRefreshToken->getToken()
        );
        $this->assertEquals(
            $data[LoginRefreshTokenModelInterface::PROPERTY_DISABLED_AT],
            $loginRefreshToken->getDisabledAt()
        );
        $this->assertEquals(
            $data[LoginRefreshTokenModelInterface::PROPERTY_USER],
            $loginRefreshToken->getUser()
        );
        $this->assertEquals(
            $data[LoginRefreshTokenModelInterface::PROPERTY_ID],
            $loginRefreshToken->getId()
        );
        $this->assertEquals(
            $data[LoginRefreshTokenModelInterface::PROPERTY_CREATED_AT],
            $loginRefreshToken->getCreatedAt()
        );
        $this->assertEquals(
            $data[LoginRefreshTokenModelInterface::PROPERTY_UPDATED_AT],
            $loginRefreshToken->getUpdatedAt()
        );
    }

    //endregion

    /**
     * @return LoginRefreshTokenModelFactoryInterface
     */
    private function getLoginRefreshTokenModelFactory(): LoginRefreshTokenModelFactoryInterface
    {
        return $this->app->get(LoginRefreshTokenModelFactoryInterface::class);
    }
}