<?php

namespace App\Middlewares;

use App\Attributes\Rules;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Sigil\Context;

class RulesMiddleware
{
    public function handle(Request $request, $next, Context $context)
    {
        try {
            if ($context->hasState(Rules::class)) {
                $request->validate($context->getState(Rules::class));
            }

            return $next($request);
        } catch (ValidationException $e) {
            return new Response([
                'error' => [
                    'exception' => 'validation',
                    'message' => $e->getMessage(),
                    'messages' => $e->errors()
                ]
            ], 400);
        }
    }
}
