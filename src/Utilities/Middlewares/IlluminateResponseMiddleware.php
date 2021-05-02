<?php

namespace Sigil\Utilities\Middlewares;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IlluminateResponseMiddleware
{
    public function handle(Request $request, $next)
    {
        $response = $next($request);

        if (is_object($response) && $response instanceof \Symfony\Component\HttpFoundation\Response)
            return $response;

        return new Response($response);
    }
}
