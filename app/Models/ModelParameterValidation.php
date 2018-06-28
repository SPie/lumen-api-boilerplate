<?php

namespace App\Models;

use App\Exceptions\InvalidParameterException;

/**
 * Trait ModelParameterValidation
 *
 * @package App\Models
 */
trait ModelParameterValidation
{

    /**
     * @param array  $data
     * @param string $parameterName
     * @param bool   $required
     *
     * @return int|null
     *
     * @throws InvalidParameterException
     */
    protected function validateIntegerParameter(array $data, string $parameterName, bool $required = true): ?int
    {
        $parameter = $this->validateEmptyParameter($data, $parameterName, $required);

        if (\is_null($parameter)) {
            return $parameter;
        }

        if (\filter_var($parameter, FILTER_VALIDATE_INT) === false) {
            throw new InvalidParameterException('Parameter ' . $parameterName . ' has to be an integer.');
        }

        return $parameter;
    }

    /**
     * @param array  $data
     * @param string $parameterName
     * @param bool   $required
     * @param bool   $allowEmptyString
     *
     * @return null|string
     *
     * @throws InvalidParameterException
     */
    protected function validateStringParameter(
        array $data,
        string $parameterName,
        bool $required = true,
        bool $allowEmptyString = false
    ): ?string
    {
        $parameter = $this->validateEmptyParameter($data, $parameterName, $required);

        if (\is_null($parameter)) {
            return $parameter;
        }

        if (!\is_string($parameter)) {
            throw new InvalidParameterException('Parameter ' . $parameterName . ' has to be string.');
        }

        if (!$allowEmptyString && empty($parameter)) {
            throw new InvalidParameterException('Parameter ' . $parameterName . ' is not allowed empty.');
        }

        return $parameter;
    }

    /**
     * @param array  $data
     * @param string $parameterName
     * @param bool   $required
     *
     * @return null|string
     *
     * @throws InvalidParameterException
     */
    protected function validateEmailParameter(array $data, string $parameterName, bool $required = true): ?string
    {
        $parameter = $this->validateEmptyParameter($data, $parameterName, $required);

        if (\is_null($parameter)) {
            return $parameter;
        }

        if (\filter_var($parameter, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidParameterException('Parameter ' . $parameterName . ' has to be an email address.');
        }

        return $parameter;
    }

    /**
     * @param array  $data
     * @param string $parameterName
     * @param bool   $required
     *
     * @return \DateTime|null
     *
     * @throws InvalidParameterException
     */
    protected function validateDateTimeParameter(array $data, string $parameterName, bool $required = true): ?\DateTime
    {
        $parameter = $this->validateEmptyParameter($data, $parameterName, $required);

        if (\is_null($parameter)) {
            return $parameter;
        }

        if (!($parameter instanceof \DateTime)) {
            throw new InvalidParameterException('Parameter ' . $parameterName . ' has to be an instance of DateTime.');
        }

        return $parameter;
    }

    /**
     * @param array  $data
     * @param string $parameterName
     * @param bool   $required
     * @param bool   $allowEmpty
     *
     * @return array|null
     *
     * @throws InvalidParameterException
     */
    protected function validateArrayParameter(
        array $data,
        string $parameterName,
        bool $required = true,
        bool $allowEmpty = false
    ): ?array
    {
        $parameter = $this->validateEmptyParameter($data, $parameterName, $required);

        if (\is_null($parameter)) {
            return $parameter;
        }

        if (!\is_array($parameter)) {
            throw new InvalidParameterException('Parameter ' . $parameterName . ' has to be array.');
        }

        if (!$allowEmpty && empty($parameter)) {
            throw new InvalidParameterException('Parameter ' . $parameterName . ' is not allowed empty.');
        }

        return $parameter;
    }

    /**
     * @param array                 $data
     * @param string                $parameterName
     * @param ModelFactoryInterface $modelFactory
     * @param string                $modelClassName
     * @param bool                  $required
     *
     * @return ModelInterface|null
     *
     * @throws InvalidParameterException
     */
    protected function validateModelParameter(
        array $data,
        string $parameterName,
        ModelFactoryInterface $modelFactory,
        string $modelClassName,
        bool $required = true
    ): ?ModelInterface
    {
        $parameter = $this->validateEmptyParameter($data, $parameterName, $required);

        if (\is_null($parameter) || ($parameter instanceof $modelClassName)) {
            return $parameter;
        }

        if (!\is_array($parameter)) {
            throw new InvalidParameterException('Parameter ' . $parameterName . ' has to be an array with model data');
        }

        return $modelFactory->create($parameter);
    }

    /**
     * @param array  $data
     * @param string $parameterName
     * @param bool   $required
     *
     * @return mixed|null
     *
     * @throws InvalidParameterException
     */
    protected function validateEmptyParameter(array $data, string $parameterName, bool $required = true)
    {
        if ($required && !isset($data[$parameterName])) {
            throw new InvalidParameterException('Parameter ' . $parameterName . ' is required.');
        }

        return $data[$parameterName] ?? null;
    }
}