<?php

namespace Ormi\Utilities\Middlewares;

use Illuminate\Http\Request;
use Ormi\Context;
use Ormi\Utilities\Attributes\Model;

class RouteModelMiddleware
{
    public function handle(Request $request, $next, Context $finding)
    {
        if ($finding->hasState(Model::class)) {
            $model = $finding->getState(Model::class);

            /** @var \Illuminate\Database\Eloquent\Model $class */
            $class = $model['class'];

            app()->instance($class, $class::findOrFail($finding->param($model['field'])));
        }

        return $next($request);
    }
}
