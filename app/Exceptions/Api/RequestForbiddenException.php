<?php

namespace App\Exceptions\Api;

use Illuminate\Http\Response;

/**
 * Class RequestForbiddenException
 *
 * @package App\Exceptions\Api
 */
class RequestForbiddenException extends ApiException
{

    /**
     * RequestForbiddenException constructor.
     */
    public function __construct()
    {
        parent::__construct(Response::HTTP_FORBIDDEN);
    }
}