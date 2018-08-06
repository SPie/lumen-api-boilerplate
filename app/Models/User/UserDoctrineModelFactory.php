<?php

namespace App\Models\User;

use App\Exceptions\InvalidParameterException;
use App\Models\Auth\LoginRefreshTokenModelFactoryInterface;
use App\Models\Auth\LoginRefreshTokenModelInterface;
use App\Models\ModelInterface;
use App\Models\ModelParameterValidation;

/**
 * Class UserDoctrineModelFactory
 *
 * @package App\Models\User
 */
class UserDoctrineModelFactory implements UserModelFactoryInterface
{

    use ModelParameterValidation;

    /**
     * @var LoginRefreshTokenModelFactoryInterface
     */
    private $loginRefreshTokenFactory;

    /**
     * @param LoginRefreshTokenModelFactoryInterface $loginRefreshTokenModelFactory
     *
     * @return UserModelFactoryInterface
     */
    public function setLoginRefreshTokenModelFactory(
        LoginRefreshTokenModelFactoryInterface $loginRefreshTokenModelFactory
    ): UserModelFactoryInterface
    {
        $this->loginRefreshTokenFactory = $loginRefreshTokenModelFactory;

        return $this;
    }

    /**
     * @return LoginRefreshTokenModelFactoryInterface
     */
    protected function getLoginRefreshTokenModelFactory(): LoginRefreshTokenModelFactoryInterface
    {
        return $this->loginRefreshTokenFactory;
    }
    /**
     * @param array $data
     *
     * @return ModelInterface
     *
     * @throws InvalidParameterException
     */
    public function create(array $data): ModelInterface
    {
        return (new UserDoctrineModel(
            $this->validateStringParameter($data, UserModelInterface::PROPERTY_EMAIL),
            $this->validateStringParameter($data,UserModelInterface::PROPERTY_PASSWORD)
        ))
            ->setId($this->validateIntegerParameter($data, UserModelInterface::PROPERTY_ID, false))
            ->setLoginRefreshTokens($this->validateLoginRefreshTokenModels($data))
            ->setCreatedAt($this->validateDateTimeParameter(
                $data,
                UserModelInterface::PROPERTY_CREATED_AT,
                false
            ))
            ->setUpdatedAt($this->validateDateTimeParameter(
                $data,
                UserModelInterface::PROPERTY_UPDATED_AT,
                false
            ))
            ->setDeletedAt($this->validateDateTimeParameter(
                $data,
                UserModelInterface::PROPERTY_DELETED_AT,
                false
            ));
    }

    /**
     * @param UserModelInterface|ModelInterface $model
     * @param array                             $data
     *
     * @return UserModelInterface|ModelInterface
     *
     * @throws InvalidParameterException
     */
    public function fill(ModelInterface $model, array $data): ModelInterface
    {
        $email = $this->validateStringParameter($data, UserModelInterface::PROPERTY_EMAIL, false);
        if (!empty($email)) {
            $model->setEmail($email);
        }

        $password = $this->validateStringParameter(
            $data,
            UserModelInterface::PROPERTY_PASSWORD,
            false
        );
        if (!empty($password)) {
            $model->setPassword($password);
        }

        $id = $this->validateIntegerParameter($data, UserModelInterface::PROPERTY_ID, false);
        if (!empty($id)) {
            $model->setId($id);
        }

        $createdAt = $this->validateDateTimeParameter(
            $data,
            UserModelInterface::PROPERTY_CREATED_AT,
            false
        );
        if (!empty($createdAt)) {
            $model->setCreatedAt($createdAt);
        }

        $updatedAt = $this->validateDateTimeParameter(
            $data,
            UserModelInterface::PROPERTY_UPDATED_AT,
            false
        );
        if (!empty($updatedAt)) {
            $model->setUpdatedAt($updatedAt);
        }

        $deletedAt = $this->validateDateTimeParameter(
            $data,
            UserModelInterface::PROPERTY_DELETED_AT,
            false
        );
        if (!empty($deletedAt)) {
            $model->setDeletedAt($deletedAt);
        }

        $model->setLoginRefreshTokens($this->validateLoginRefreshTokenModels($data));

        return $model;
    }

    /**
     * @param array $data
     *
     * @return UserModelInterface[]
     *
     * @throws InvalidParameterException
     */
    protected function validateLoginRefreshTokenModels(array $data): array
    {
        $refreshTokens = $this->validateArrayParameter(
            $data,
            UserModelInterface::PROPERTY_LOGIN_REFRESH_TOKENS,
            false
        );

        return \is_array($refreshTokens)
            ? \array_map(
                function ($refreshToken) {
                    if ($refreshToken instanceof LoginRefreshTokenModelInterface) {
                        return $refreshToken;
                    }

                    if (!\is_array($refreshToken)) {
                        throw new InvalidParameterException('RefreshLoginToken data has to be array.');
                    }

                    return $this->getLoginRefreshTokenModelFactory()->create($refreshToken);
                },
                $refreshTokens
            )
            : [];
    }
}