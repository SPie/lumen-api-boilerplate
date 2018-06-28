<?php

namespace App\Services\User;

use App\Exceptions\InvalidParameterException;
use App\Exceptions\ModelNotFoundException;
use App\Models\User\UserModelFactoryInterface;
use App\Models\User\UserModelInterface;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

/**
 * Class UsersService
 *
 * @package App\Services\User
 */
class UsersService implements UsersServiceInterface
{

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var UserModelFactoryInterface
     */
    private $userModelFactory;

    /**
     * UsersService constructor.
     *
     * @param UserRepositoryInterface   $userRepository
     * @param UserModelFactoryInterface $userModelFactory
     */
    public function __construct(UserRepositoryInterface $userRepository, UserModelFactoryInterface $userModelFactory)
    {
        $this->userRepository = $userRepository;
        $this->userModelFactory = $userModelFactory;
    }

    /**
     * @return UserRepositoryInterface
     */
    protected function getUserRepository(): UserRepositoryInterface
    {
        return $this->userRepository;
    }

    /**
     * @return UserModelFactoryInterface
     */
    protected function getUserModelFactory(): UserModelFactoryInterface
    {
        return $this->userModelFactory;
    }

    /**
     * @param int $id
     *
     * @return UserModelInterface
     *
     * @throws ModelNotFoundException
     */
    public function getUser(int $id): UserModelInterface
    {
        $user = $this->getUserRepository()->find($id);
        if (!$user) {
            throw new ModelNotFoundException(UserModelInterface::class, $id);
        }

        return $user;
    }

    /**
     * @param string $email
     * @param string $password
     *
     * @return UserModelInterface
     *
     * @throws AuthorizationException
     */
    public function validateUserCredentials(string $email, string $password): UserModelInterface
    {
        $user = $this->getUserRepository()->findOneByEmail($email);

        if (!($user && Hash::check($password, $user->getAuthPassword()))) {
            throw new AuthorizationException();
        }

        return $user;
    }

    /**
     * @return UserModelInterface[]|Collection
     */
    public function listUsers(): Collection
    {
        return $this->getUserRepository()->findAll();
    }

    /**
     * @param array $userData
     *
     * @return UserModelInterface
     *
     * @throws InvalidParameterException
     */
    public function createUser(array $userData): UserModelInterface
    {
        $user = $this->getUserModelFactory()->create($userData);
        if ($this->userExists($user)) {
            throw new InvalidParameterException();
        }

        return $this->getUserRepository()->save($user);
    }

    /**
     * @param int   $userId
     * @param array $userData
     *
     * @return UserModelInterface
     *
     * @throws InvalidParameterException
     */
    public function editUser(int $userId, array $userData): UserModelInterface
    {
        $user = $this->getUserModelFactory()->fill($this->getUser($userId), $userData);
        if ($this->userExists($user, $userId)) {
            throw new InvalidParameterException();
        }

        return $this->getUserRepository()->save($user);
    }

    /**
     * @param int $userId
     *
     * @return $this
     */
    public function deleteUser(int $userId)
    {
        $this->getUserRepository()->delete($this->getUser($userId));

        return $this;
    }

    /**
     * @param UserModelInterface $user
     * @param int|null           $userId
     *
     * @return bool
     */
    protected function userExists(UserModelInterface $user, int $userId = null): bool
    {
        $user = $this->getUserRepository()->findOneByEmail($user->getEmail());

        return ($user && $user->getId() != $userId);
    }
}