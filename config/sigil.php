<?php

use Sigil\Utilities\Middlewares\RouteModelMiddleware;
use Sigil\Utilities\Middlewares\RouteResolverMiddleware;
use Sigil\Utilities\Middlewares\TransformerDecorator;

return [
    'root_controller' => \App\Http\Controllers\RootController::class,
    'middlewares' => [
        RouteModelMiddleware::class,
        RouteResolverMiddleware::class
    ],
    'decorators' => [
        TransformerDecorator::class
    ],
    'auto_reload' => true
];
