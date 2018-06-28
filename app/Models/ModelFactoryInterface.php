<?php

namespace App\Models;

use App\Exceptions\InvalidParameterException;

/**
 * Interface ModelFactoryInterface
 *
 * @package App\Models
 */
interface ModelFactoryInterface
{

    /**
     * @param array $data
     *
     * @return ModelInterface
     *
     * @throws InvalidParameterException
     */
    public function create(array $data): ModelInterface;

    /**
     * @param ModelInterface $model
     * @param array          $data
     *
     * @return ModelInterface
     *
     * @throws InvalidParameterException
     */
    public function fill(ModelInterface $model, array $data): ModelInterface;
}