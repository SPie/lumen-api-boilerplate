<?php

namespace App\Exceptions;

/**
 * Class ModelNotFoundException
 *
 * @package App\Exceptions
 */
class ModelNotFoundException extends \Exception
{

    /**
     * @var string
     */
    private $modelClass;

    /**
     * NotAllowedException constructor.
     *
     * @param string      $modelClass
     * @param string|null $identifier
     */
    public function __construct(string $modelClass, string $identifier = null)
    {
        $this->modelClass = $modelClass;

        parent::__construct(
            $this->modelClass . ' ' . (!empty($identifier) ? $identifier . ' ' : '') .  'not found.'
        );
    }

    /**
     * @return string
     */
    public function getModelClass(): string
    {
        return $this->modelClass;
    }

    /**
     * @param string $modelClass
     *
     * @return bool
     */
    public function modelInstanceOf(string $modelClass): bool
    {
        return $this->getModelClass() === $modelClass;
    }
}