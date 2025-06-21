<?php

namespace App\Http\Controllers;

use App\Exceptions\Routing\RouteNotFoundException;
use App\Middlewares\CorsMiddleware;
use App\Middlewares\ExceptionHandlingMiddleware;
use App\Middlewares\TransformerDecorator;
use Exedra\Routeller\Attributes\AsFailRoute;
use Exedra\Routeller\Attributes\Decorator;
use Exedra\Routeller\Attributes\Middleware;
use Exedra\Routeller\Attributes\Path;
use Exedra\Routeller\Attributes\Requestable;

#[Middleware(CorsMiddleware::class)]
#[Middleware(ExceptionHandlingMiddleware::class)]
#[Decorator(TransformerDecorator::class)]
#[Path('/apis')]
class ApisController extends \Sigil\Controller
{
    #[Path('/hello-world')]
    public function getHelloWorld()
    {
        return [
            'message' => 'Hello, World!'
        ];
    }

    #[AsFailRoute]
    #[Requestable(false)]
    public function getError()
    {
        throw new RouteNotFoundException();
    }
}
