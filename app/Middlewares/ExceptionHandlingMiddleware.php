<?php

namespace App\Middlewares;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ExceptionHandlingMiddleware
{
    public function handle(Request $request, $next)
    {
        try {
            return $next($request);
        } catch (ModelNotFoundException $e) {
            return new Response([
                'error' => [
                    'code' => 'resource_not_found',
                    'message' => 'Resource not found'
                ]
            ], 404);
        } catch (\Exception $e) {
            $exception = explode("\\", $e::class);

            $code = array_pop($exception);

            $code = str_replace('_exception', '', Str::snake($code, '_'));

            return new Response([
                'error' => [
                    'code' => $code,
                    'message' => $e->getMessage() ? $e->getMessage() : $code
                ]
            ], 400);
        }
    }
}
