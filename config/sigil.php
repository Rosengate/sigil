<?php

use Sigil\Utilities\Middlewares\TransformerDecorator;

return [
    'root_controller' => \App\Http\Controllers\RootController::class,
    'decorators' => [
        TransformerDecorator::class
    ],
    'auto_reload' => true
];
