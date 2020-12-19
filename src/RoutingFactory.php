<?php

namespace Sigil;

use Exedra\Routing\Factory;
use Exedra\Routing\Route;
use Psr\Http\Message\ServerRequestInterface;

class RoutingFactory extends Factory
{
    private \Closure $middlewareFactory;

    public function __construct(\Closure $middlewareFactory)
    {
        parent::__construct();
        $this->middlewareFactory = $middlewareFactory;
    }

    public function createFinding(Route $route = null, array $parameters = null, ServerRequestInterface $request = null)
    {
        return new Context($route, $parameters, $request, $this->middlewareFactory);
    }
}
