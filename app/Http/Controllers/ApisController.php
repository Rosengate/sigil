<?php

namespace App\Http\Controllers;

use App\Middlewares\CorsMiddleware;
use App\Middlewares\ExceptionHandlingMiddleware;
use App\Middlewares\TransformerDecorator;
use Exedra\Routeller\Attributes\Decorator;
use Exedra\Routeller\Attributes\Middleware;

#[Middleware(CorsMiddleware::class)]
#[Middleware(ExceptionHandlingMiddleware::class)]
#[Decorator(TransformerDecorator::class)]
class ApisController extends \Sigil\Controller
{
}
