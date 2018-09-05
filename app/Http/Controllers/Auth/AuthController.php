<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User\UserModelInterface;
use App\Services\JWT\JWTServiceInterface;
use App\Services\User\UsersServiceInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Class AuthController
 *
 * @package App\Http\Controllers\Auth
 */
class AuthController extends Controller
{

    const ROUTE_NAME_LOGIN  = 'auth.login';
    const ROUTE_NAME_LOGOUT = 'auth.logout';
    const ROUTE_NAME_USER   = 'auth.user';

    const RESPONSE_PARAMETER_USER = 'user';

    /**
     * @var Guard
     */
    private $guard;

    /**
     * @var JWTServiceInterface
     */
    private $jwtService;

    /**
     * AuthController constructor.
     *
     * @param Guard               $guard
     * @param JWTServiceInterface $jwtService
     */
    public function __construct(Guard $guard, JWTServiceInterface $jwtService)
    {
        $this->guard = $guard;
        $this->jwtService = $jwtService;
    }

    /**
     * @return Guard
     */
    protected function getGuard(): Guard
    {
        return $this->guard;
    }

    /**
     * @return JWTServiceInterface
     */
    protected function getJwtService(): JWTServiceInterface
    {
        return $this->jwtService;
    }

    //region Controller actions

    /**
     * @param Request               $request
     * @param UsersServiceInterface $usersService
     *
     * @return JsonResponse|Response
     *
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function login(Request $request, UsersServiceInterface $usersService): JsonResponse
    {
        $credentials = $this->validate($request, [
            UserModelInterface::PROPERTY_EMAIL => [
                'required',
            ],
            UserModelInterface::PROPERTY_PASSWORD => [
                'required',
            ],
        ]);

        $user = $usersService->validateUserCredentials(
            $credentials[UserModelInterface::PROPERTY_EMAIL],
            $credentials[UserModelInterface::PROPERTY_PASSWORD]
        );

        $jwtObject = $this->getJwtService()->createToken($user);

        $this->getGuard()->setUser($user);

        return $this->getJwtService()->response(
            $this->createResponse([], Response::HTTP_NO_CONTENT),
            $jwtObject
        );
    }

    /**
     * @return JsonResponse
     *
     * @throws AuthorizationException
     */
    public function logout(): JsonResponse
    {
        /** @var UserModelInterface $user */
        $user = $this->getGuard()->user();
        if (!$user) {
            throw new AuthorizationException();
        }

        $this->getJwtService()->deauthenticate($user);

        return $this->createResponse([], Response::HTTP_NO_CONTENT);
    }

    /**
     * @return JsonResponse
     *
     * @throws AuthorizationException
     */
    public function authenticatedUser(): JsonResponse
    {
        $user = $this->getGuard()->user();
        if (!$user) {
            throw new AuthorizationException();
        }

        return $this->createResponse([
            self::RESPONSE_PARAMETER_USER => $user,
        ]);
    }

    //endregion
}