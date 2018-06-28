<?php

namespace App\Http\Controllers;

use App\Http\Response\JsonResponseData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Laravel\Lumen\Http\ResponseFactory;
use Laravel\Lumen\Routing\Controller as BaseController;

/**
 * Class Controller
 *
 * @package App\Http\Controllers
 */
class Controller extends BaseController
{

    /**
     * @param array $data
     * @param int   $statusCode
     * @param array $headers
     * @param int   $options
     *
     * @return JsonResponse
     */
    protected function createResponse(
        array $data = [],
        int $statusCode = Response::HTTP_OK,
        array $headers = [],
        int $options = 0
    ): JsonResponse
    {
        return (new ResponseFactory())->json(
            new JsonResponseData($data),
            $statusCode,
            $headers,
            $options
        );
    }
}
