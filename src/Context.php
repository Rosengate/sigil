<?php

namespace Ormi;

use Exedra\Routing\Finding;
use Exedra\Routing\Route;
use Psr\Http\Message\ServerRequestInterface;

class Context extends Finding
{
    /**
     * @var \Closure|null
     */
    protected $middlewareFactory;

    public function __construct(Route $route, array $parameters = array(), ServerRequestInterface $request = null, \Closure $middlewareFactory = null)
    {
        $this->middlewareFactory = $middlewareFactory;

        parent::__construct($route, $parameters, $request);
    }

    protected function createMiddleware($class)
    {
        return call_user_func_array($this->middlewareFactory, [$class]);
    }
}
