<?php

namespace App\Repositories\User;

use App\Models\ModelInterface;
use App\Models\User\UserModelInterface;
use App\Repositories\AbstractDoctrineRepository;

/**
 * Class UserDoctrineRepository
 *
 * @package App\Repositories\User
 */
class UserDoctrineRepository extends AbstractDoctrineRepository implements UserRepositoryInterface
{

    /**
     * @param string $email
     *
     * @return UserModelInterface|ModelInterface|null
     */
    public function findOneByEmail(string $email): ?UserModelInterface
    {
        return $this->findOneBy([UserModelInterface::PROPERTY_EMAIL => $email]);
    }
}