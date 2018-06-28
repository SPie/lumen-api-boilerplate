<?php

namespace App\Services\User;

use App\Exceptions\ModelNotFoundException;
use App\Models\User\UserModelInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

/**
 * Interface UsersServiceInterface
 *
 * @package App\Services\User
 */
interface UsersServiceInterface
{

    /**
     * @param int $id
     *
     * @return UserModelInterface
     *
     * @throws ModelNotFoundException
     */
    public function getUser(int $id): UserModelInterface;

    /**
     * @return UserModelInterface[]|Collection
     */
    public function listUsers(): Collection;

    /**
     * @param array $userData
     *
     * @return UserModelInterface
     */
    public function createUser(array $userData): UserModelInterface;

    /**
     * @param int   $userId
     * @param array $userData
     *
     * @return UserModelInterface
     */
    public function editUser(int $userId, array $userData): UserModelInterface;

    /**
     * @param int $userId
     *
     * @return $this
     */
    public function deleteUser(int $userId);

    /**
     * @param string $email
     * @param string $password
     *
     * @return UserModelInterface
     *
     * @throws AuthorizationException
     */
    public function validateUserCredentials(string $email, string $password): UserModelInterface;
}