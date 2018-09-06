<?php
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\User\UsersController;
use Laravel\Lumen\Routing\Router;

/**
 * @var Router $router
 */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->group(
    [
        'prefix'     => 'api',
        'middleware' => [
            'signature',
        ],
    ],
    function (Router $router) {

        //region Auth calls

        $router->group(['prefix' => 'auth'], function (Router $router) {

            $router->get(
                'user',
                [
                    'as'         => AuthController::ROUTE_NAME_USER,
                    'uses'       => 'Auth\AuthController@authenticatedUser',
                    'middleware' => ['refresh'],
                ]
            );
            $router->post(
                'logout',
                [
                    'as'         => AuthController::ROUTE_NAME_LOGOUT,
                    'uses'       => 'Auth\AuthController@logout',
                    'middleware' => ['auth'],
                ]
            );
            $router->post('login', ['as' => AuthController::ROUTE_NAME_LOGIN, 'uses' => 'Auth\AuthController@login']);
        });

        //endregion

        //region Users calls

        $router->group(
            [
                'prefix'     => 'users',
                'middleware' => ['refresh'],
            ],
            function (Router $router) {

                $router->get('', ['as' => UsersController::ROUTE_NAME_LIST, 'uses' => 'User\UsersController@listUsers']);
                $router->post('', ['as' => UsersController::ROUTE_NAME_CREATE, 'uses' => 'User\UsersController@createUser']);
                $router->get(
                    '{userId}',
                    [
                        'as' => UsersController::ROUTE_NAME_DETAILS,
                        'uses' => 'User\UsersController@userDetails'
                    ]
                );
                $router->put(
                    '{userId}',
                    [
                        'as' => UsersController::ROUTE_NAME_EDIT,
                        'uses' => 'User\UsersController@editUser'
                    ]
                );
                $router->delete(
                    '{userId}',
                    [
                        'as' => UsersController::ROUTE_NAME_DELETE,
                        'uses' => 'User\UsersController@deleteUser'
                    ]
                );
            }
        );

        //endregion

    }
);