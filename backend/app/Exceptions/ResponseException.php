<?php

namespace App\Exceptions;

use Illuminate\Http\Response;

use Exception;

class ResponseException extends Exception
{
    protected $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function render()
    {
        return $this->response;
    }
}
