<?php

namespace App\Exceptions\Api;

use Illuminate\Http\Response;

/**
 * Class InvalidSignatureException
 *
 * @package App\Exceptions\Api
 */
class InvalidSignatureException extends ApiException
{

    /**
     * InvalidSignatureException constructor.
     *
     * @param string $message
     */
    public function __construct(string $message = '')
    {
        parent::__construct(Response::HTTP_NOT_ACCEPTABLE, [], $message);
    }
}