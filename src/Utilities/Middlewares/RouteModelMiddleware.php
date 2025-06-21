<?php

namespace Sigil\Utilities\Middlewares;

use Illuminate\Http\Request;
use Sigil\Context;
use Sigil\Utilities\Attributes\Model;

/**
 * @deprecated
 */
class RouteModelMiddleware
{
    public function handle(Request $request, $next, Context $finding)
    {
        if ($finding->hasState(Model::class)) {
            $model = $finding->getState(Model::class);

            /** @var \Illuminate\Database\Eloquent\Model $class */
            $class = $model['class'];

            if (isset($model['field']) && $model['field'] != 'id') {
                app()->instance($class, $class::query()->where($model['field'], $finding->param($model['param']))->firstOrFail());
            } else {
                app()->instance($class, $class::findOrFail($finding->param($model['param'])));
            }
        }

        return $next($request);
    }
}
