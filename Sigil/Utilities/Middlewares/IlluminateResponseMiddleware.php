<?php

namespace Sigil\Utilities\Middlewares;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IlluminateResponseMiddleware
{
    public function handle(Request $request, $next)
    {
        return new Response($next($request));
    }
}
