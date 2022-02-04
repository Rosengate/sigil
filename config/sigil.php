<?php

use App\Http\Controllers\RootController;
use Sigil\Utilities\Middlewares\IlluminateValidationMiddleware;
use Sigil\Utilities\Middlewares\RendererDecorator;
use Sigil\Utilities\Middlewares\RouteModelMiddleware;
use Sigil\Utilities\Middlewares\RouteResolverMiddleware;
use Sigil\Utilities\Middlewares\TransformerDecorator;

return [
    'root_controller' => RootController::class,
    'middlewares' => [
        RouteModelMiddleware::class,
        RouteResolverMiddleware::class,
        IlluminateValidationMiddleware::class
    ],
    'decorators' => [
        TransformerDecorator::class,
        RendererDecorator::class
    ],
    'auto_reload' => true
];
