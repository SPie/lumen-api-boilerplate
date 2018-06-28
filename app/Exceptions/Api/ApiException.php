<?php

namespace App\Exceptions\Api;

use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Http\ResponseFactory;

/**
 * Class ApiException
 *
 * @package App\Exceptions\Api
 */
class ApiException extends \Exception
{

    /**
     * @var int
     */
    protected $statusCode;

    /**
     * @var array
     */
    protected $responseData;

    /**
     * ApiException constructor.
     *
     * @param int    $statusCode
     * @param array  $responseData
     * @param string $message
     */
    public function __construct(int $statusCode, array $responseData = [], string $message = '')
    {
        $this->statusCode = $statusCode;
        $this->responseData = $responseData;

        parent::__construct($message, $statusCode);
    }

    /**
     * @return int
     */
    protected function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array
     */
    protected function getResponseData(): array
    {
        return $this->responseData;
    }

    /**
     * @return JsonResponse
     */
    public function getResponse(): JsonResponse
    {
        return (new ResponseFactory())->json(
            $this->getResponseData(),
            $this->getStatusCode()
        );
    }
}