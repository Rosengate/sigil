<?php

namespace Sigil\Utilities\Middlewares;

use Illuminate\Http\Request;
use Sigil\Context;
use Sigil\Utilities\Attributes\Validation;

class IlluminateValidationMiddleware
{
    public function handle(Request $request, $next, Context $context)
    {
        if ($context->hasState(Validation::class))
            $request->validate($context->getState(Validation::class));

        return $next($request);
    }
}
