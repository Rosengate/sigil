<?php

namespace Sigil\Utilities\Middlewares;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Sigil\Context;

class RouteResolverMiddleware
{
    public function handle(Request $request, $next, Context $context)
    {
        $route = null;

        $request->setRouteResolver(function() use ($request, $context, &$route) {
            if ($route)
                return $route;

            $route = new Route($context->route->getMethod(), $context->route->getPath(true), null);

            $route->bind($request);

            foreach ($context->getParameters() as $name => $value)
                $route->setParameter($name, $value);

            return $route;
        });

        return $next($request);
    }
}
