<?php

namespace App\Http\Controllers\User;

use App\Exceptions\Api\ApiException;
use App\Exceptions\Api\RequestForbiddenException;
use App\Exceptions\ModelNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\User\UserDoctrineModel;
use App\Models\User\UserModelInterface;
use App\Services\User\UsersServiceInterface;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class UsersController
 *
 * @package App\Http\Controllers\User
 */
class UsersController extends Controller
{

    const ROUTE_NAME_LIST    = 'users.list';
    const ROUTE_NAME_DETAILS = 'users.details';
    const ROUTE_NAME_CREATE  = 'users.create';
    const ROUTE_NAME_EDIT    = 'users.edit';
    const ROUTE_NAME_DELETE  = 'users.delete';

    const RESPONSE_PARAMETER_USERS = 'users';
    const RESPONSE_PARAMETER_USER  = 'user';

    /**
     * @var UsersServiceInterface
     */
    private $usersService;

    /**
     * UsersController constructor.
     *
     * @param UsersServiceInterface $usersService
     */
    public function __construct(UsersServiceInterface $usersService)
    {
        $this->usersService = $usersService;
    }

    /**
     * @return UsersServiceInterface
     */
    protected function getUserService(): UsersServiceInterface
    {
        return $this->usersService;
    }

    //region Controller actions

    /**
     * @return JsonResponse
     */
    public function listUsers(): JsonResponse
    {
        return $this->createResponse([
            self::RESPONSE_PARAMETER_USERS => $this->getUserService()->listUsers(),
        ]);
    }

    /**
     * @param int $userId
     *
     * @return JsonResponse
     */
    public function userDetails(int $userId): JsonResponse
    {
        try {
            return $this->createResponse([
                self::RESPONSE_PARAMETER_USER => $this->getUserService()->getUser($userId),
            ]);
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException('User with id #' . $userId . ' not found.');
        }
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createUser(Request $request): JsonResponse
    {
        return $this->createResponse(
            [
                self::RESPONSE_PARAMETER_USER => $this->getUserService()->createUser(
                    $this->validate($request, $this->getUserDataValidators())
                )
            ],
            Response::HTTP_CREATED
        );
    }

    /**
     * @param int     $userId
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function editUser(int $userId, Request $request): JsonResponse
    {
        try {
            return $this->createResponse([
                self::RESPONSE_PARAMETER_USER => $this->getUserService()->editUser(
                    $userId,
                    $this->validate($request, $this->getUserDataValidators(false, $userId))
                )
            ]);
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException('User with id #' . $userId . ' not found.');
        }
    }

    /**
     * @param int   $userId
     * @param Guard $guard
     *
     * @return JsonResponse
     *
     * @throws RequestForbiddenException
     */
    public function deleteUser(int $userId, Guard $guard): JsonResponse
    {
        if ($userId == $guard->id()) {
            throw new RequestForbiddenException();
        }

        try {
            $this->getUserService()->deleteUser($userId);
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException('User with id #' . $userId . ' not found.');
        }

        return $this->createResponse([], Response::HTTP_NO_CONTENT);
    }

    //endregion

    /**
     * @param bool     $required
     * @param int|null $userId
     *
     * @return array
     */
    protected function getUserDataValidators(bool $required = true, int $userId = null): array
    {
        return [
            UserModelInterface::PROPERTY_EMAIL => [
                'email',
                'filled',
                $required
                    ? 'required'
                    : null,
                Rule::unique(UserDoctrineModel::class, UserModelInterface::PROPERTY_EMAIL)
                    ->ignore($userId), // TODO
            ],
            UserModelInterface::PROPERTY_PASSWORD => [
                'filled',
                $required
                    ? 'required'
                    : null,
            ]
        ];
    }
}