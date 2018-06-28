<?php

namespace App\Repositories;

use App\Models\ModelInterface;
use Doctrine\Common\Collections\Criteria;
use Illuminate\Support\Collection;

/**
 * Interface RepositoryInterface
 *
 * @package App\Repositories
 */
interface RepositoryInterface
{


    /**
     * @param int $id
     *
     * @return ModelInterface|null
     */
    public function find($id): ?ModelInterface;

    /**
     * @return Collection
     */
    public function findAll(): Collection;

    /**
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return Collection
     */
    public function findBy(array $criteria = [], array $orderBy = null, $limit = null, $offset = null): Collection;

    /**
     * @param array $criteria
     *
     * @return ModelInterface|null
     */
    public function findOneBy(array $criteria): ?ModelInterface;

    /**
     * @param Criteria $criteria
     *
     * @return Collection
     */
    public function findByCriteria(Criteria $criteria): Collection;

    /**
     * @param ModelInterface $model
     * @param bool           $flush
     *
     * @return ModelInterface
     */
    public function save(ModelInterface $model, bool $flush = true): ModelInterface;

    /**
     * @param ModelInterface $model
     * @param bool           $flush
     *
     * @return $this
     */
    public function delete(ModelInterface $model, bool $flush = true);

    /**
     * @return $this
     */
    public function flush();
}