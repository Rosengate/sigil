<?php

namespace App\Middlewares;

use App\Attributes\BindModel;
use Illuminate\Http\Request;
use Sigil\Context;
use Sigil\Utilities\Attributes\Model;

class RouteModelMiddleware
{
    public function handle(Request $request, $next, Context $finding)
    {
        $changes = [];

        foreach ($finding->getSeries(BindModel::class) as $model) {
            /** @var \Illuminate\Database\Eloquent\Model $class */
            $class = $model['class'];

            if (isset($model['field']) && $model['field'] != 'id') {
                app()->instance($class, $class::query()->where($model['field'], $finding->param($model['param']))->firstOrFail());
            } else {
                $changes[] = $model;

                app()->instance($class, $class::findOrFail($finding->param($model['param'])));
            }
        }

        $response = $next($request);

//        $method = strtolower($request->method());
//
//        if (count($changes) > 0 && in_array($method, ['post', 'delete']) && app()->has(CompanyModel::class) && app()->has(EmployeeModel::class)) {
//            $path = trim($finding->route->getPath(), '/');
//
//            $defaultEvents = [
//                'post' => 'update',
//                'delete' => 'delete'
//            ];
//
//            $event = !$path ? $defaultEvents[$method] : $path;
//
//            $model = array_pop($changes);
//
//            $company = app()->get(CompanyModel::class);
//            $employee = app()->get(EmployeeModel::class);
//
//            CompanyLogModel::createLog($company, $employee, $event, $model['class'], $finding->param($model['param']));
//        }

        return $response;
    }
}
