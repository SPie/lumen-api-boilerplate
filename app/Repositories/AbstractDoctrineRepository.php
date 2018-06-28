<?php

namespace App\Repositories;

use App\Models\ModelInterface;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\LazyCriteriaCollection;
use Doctrine\ORM\Persisters\Entity\EntityPersister;
use Illuminate\Support\Collection;

/**
 * Class AbstractDoctrineRepository
 *
 * @package App\Repositories
 */
abstract class AbstractDoctrineRepository implements RepositoryInterface
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var string
     */
    private $className;

    /**
     * AbstractDoctrineRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ClassMetadata          $classMetadata
     */
    public function __construct(EntityManagerInterface $entityManager, ClassMetadata $classMetadata)
    {
        $this->entityManager = $entityManager;
        $this->className     = $classMetadata->getName();
    }

    /**
     * @return EntityManagerInterface
     */
    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param int $id
     *
     * @return ModelInterface|null
     */
    public function find($id): ?ModelInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getEntityManager()->find($this->getClassName(), $id);
    }

    /**
     * @return Collection
     */
    public function findAll(): Collection
    {
        return $this->findBy();
    }

    /**
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return Collection
     */
    public function findBy(
        array $criteria = [],
        array $orderBy = null,
        $limit = null,
        $offset = null
    ): Collection
    {
        return new Collection(
            $this->getEntityPersister()->loadAll(
                $criteria,
                $orderBy,
                $limit,
                $offset
            )
        );
    }

    /**
     * @param array $criteria
     *
     * @return ModelInterface|null
     */
    public function findOneBy(array $criteria): ?ModelInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getEntityPersister()->load($criteria);
    }

    /**
     * @param Criteria $criteria
     *
     * @return Collection
     */
    public function findByCriteria(Criteria $criteria): Collection
    {
        return new Collection((new LazyCriteriaCollection($this->getEntityPersister(), $criteria))->getValues());
    }

    /**
     * @param ModelInterface $model
     * @param bool           $flush
     *
     * @return ModelInterface
     */
    public function save(ModelInterface $model, bool $flush = true): ModelInterface
    {
        $this->getEntityManager()->persist($model);

        if ($flush) {
            $this->getEntityManager()->flush();
        }

        return $model;
    }

    /**
     * @param ModelInterface $model
     * @param bool           $flush
     *
     * @return $this
     */
    public function delete(ModelInterface $model, bool $flush = true)
    {
        $this->getEntityManager()->remove($model);

        if ($flush) {
            $this->getEntityManager()->flush();
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function flush()
    {
        $this->getEntityManager()->flush();

        return $this;
    }

    /**
     * @return EntityPersister
     */
    protected function getEntityPersister(): EntityPersister
    {
        return $this->getEntityManager()->getUnitOfWork()->getEntityPersister($this->getClassName());
    }
}