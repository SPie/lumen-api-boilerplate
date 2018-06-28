<?php

namespace App\Exceptions;

use App\Exceptions\Api\ApiException;
use App\Exceptions\Api\NotReportable;
use Exception;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Laravel\Lumen\Http\ResponseFactory;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class Handler
 *
 * @package App\Exceptions
 */
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        NotReportable::class
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response|JsonResponse
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof ApiException) {
            return $e->getResponse();
        }

        if ($e instanceof HttpException) {
            return (new ResponseFactory())->json([], $e->getStatusCode(), $e->getHeaders());
        }

        if ($e instanceof ValidationException) {
            return parent::render($request, $e);
        }

        if ($e instanceof AuthorizationException) {
            return (new ResponseFactory())->json([$e->getMessage()], Response::HTTP_UNAUTHORIZED);
        }

        //messages for dev
        if (env('APP_DEBUG', false)) {
            return (new ResponseFactory())->json(
                [
                    $e->getMessage(),
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return (new ResponseFactory())->json(
            [
                'Internal Server Error',
            ],
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }
}
