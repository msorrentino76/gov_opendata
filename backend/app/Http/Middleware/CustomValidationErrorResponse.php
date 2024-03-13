<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class CustomValidationErrorResponse
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if ($response->exception instanceof ValidationException) {
            return $this->handleValidationException($response->exception);
        }

        return $response;
    }

    protected function handleValidationException(ValidationException $exception): Response
    {
        $errors = $exception->validator->errors()->toArray();

        $erroreVue3 = [];
        
        foreach ($errors as $fields => $errorsArray) {
            $erroreVue3[$fields] = implode(',', $errorsArray);
        }
        return response()->json([
            'errors' => $erroreVue3,
        ], 422); 

    }
}
