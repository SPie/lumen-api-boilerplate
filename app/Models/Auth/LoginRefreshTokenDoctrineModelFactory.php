<?php

namespace App\Models\Auth;

use App\Exceptions\InvalidParameterException;
use App\Models\ModelInterface;
use App\Models\ModelParameterValidation;
use App\Models\User\UserModelFactoryInterface;
use App\Models\User\UserModelInterface;

/**
 * Class LoginRefreshTokenDoctrineModelFactory
 *
 * @package App\Models\Auth
 */
class LoginRefreshTokenDoctrineModelFactory implements LoginRefreshTokenModelFactoryInterface
{

    use ModelParameterValidation;

    /**
     * @var UserModelFactoryInterface
     */
    private $userModelFactory;

    /**
     * @param UserModelFactoryInterface $userModelFactory
     *
     * @return LoginRefreshTokenDoctrineModelFactory
     */
    public function setUserModelFactory(
        UserModelFactoryInterface $userModelFactory
    ): LoginRefreshTokenModelFactoryInterface
    {
        $this->userModelFactory = $userModelFactory;

        return $this;
    }

    /**
     * @return UserModelFactoryInterface
     */
    protected function getUserModelFactory(): UserModelFactoryInterface
    {
        return $this->userModelFactory;
    }

    /**
     * @param array $data
     *
     * @return LoginRefreshTokenModelInterface|ModelInterface
     *
     * @throws InvalidParameterException
     */
    public function create(array $data): ModelInterface
    {
        return (new LoginRefreshTokenDoctrineModel(
            $this->validateStringParameter($data, LoginRefreshTokenModelInterface::PROPERTY_TOKEN),
            $this->validateDateTimeParameter(
                $data,
                LoginRefreshTokenModelInterface::PROPERTY_DISABLED_AT,
                false
            ),
            $this->validateUserModelParameter($data)
        ))
            ->setId($this->validateIntegerParameter(
                $data,
                LoginRefreshTokenModelInterface::PROPERTY_ID,
                false
            ))
            ->setCreatedAt($this->validateDateTimeParameter(
                $data,
                LoginRefreshTokenModelInterface::PROPERTY_CREATED_AT,
                false
            ))
            ->setUpdatedAt($this->validateDateTimeParameter(
                $data,
                LoginRefreshTokenModelInterface::PROPERTY_UPDATED_AT,
                false
            ));
    }

    /**
     * @param LoginRefreshTokenModelInterface|ModelInterface $model
     * @param array                                          $data
     *
     * @return LoginRefreshTokenModelInterface|ModelInterface
     *
     * @throws InvalidParameterException
     */
    public function fill(ModelInterface $model, array $data): ModelInterface
    {
        $token = $this->validateStringParameter(
            $data,
            LoginRefreshTokenModelInterface::PROPERTY_TOKEN,
            false
        );
        if (!empty($token)) {
            $model->setToken($token);
        }

        $disabledAt = $this->validateDateTimeParameter(
            $data,
            LoginRefreshTokenModelInterface::PROPERTY_DISABLED_AT,
            false
        );
        if (!empty($disabledAt)) {
            $model->setDisabledAt($disabledAt);
        }

        $user = $this->validateUserModelParameter($data);
        if (!empty($user)) {
            $model->setUser($user);
        }

        $id = $this->validateIntegerParameter(
            $data,
            LoginRefreshTokenModelInterface::PROPERTY_ID,
            false
        );
        if (!empty($id)) {
            $model->setId($id);
        }

        $createdAt = $this->validateDateTimeParameter(
            $data,
            LoginRefreshTokenModelInterface::PROPERTY_CREATED_AT,
            false
        );
        if (!empty($createdAt)) {
            $model->setCreatedAt($createdAt);
        }

        $updatedAt = $this->validateDateTimeParameter(
            $data,
            LoginRefreshTokenModelInterface::PROPERTY_UPDATED_AT,
            false
        );
        if (!empty($updatedAt)) {
            $model->setUpdatedAt($updatedAt);
        }

        return $model;
    }

    /**
     * @param array $data
     *
     * @return UserModelInterface|ModelInterface|null
     *
     * @throws InvalidParameterException
     */
    protected function validateUserModelParameter(array $data): ?UserModelInterface
    {
        return $this->validateModelParameter(
            $data,
            LoginRefreshTokenModelInterface::PROPERTY_USER,
            $this->getUserModelFactory(),
            UserModelInterface::class,
            false
        );
    }
}