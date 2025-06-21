<?php

use App\Http\Controllers\RootController;
use Sigil\Utilities\Middlewares\IlluminateValidationMiddleware;
use Sigil\Utilities\Middlewares\RendererDecorator;
use Sigil\Utilities\Middlewares\RouteResolverMiddleware;

return [
    'root_controller' => RootController::class,
    'middlewares' => [
        RouteResolverMiddleware::class,
        IlluminateValidationMiddleware::class
    ],
    'decorators' => [
        RendererDecorator::class
    ],
    'auto_reload' => true
];
