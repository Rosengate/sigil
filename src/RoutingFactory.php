<?php

namespace Sigil;

use Exedra\Routing\Factory;
use Exedra\Routing\Route;
use Psr\Http\Message\ServerRequestInterface;
use Sigil\Routing\Group;

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

    /**
     * Create routing group object
     * @param array $routes
     * @param \Exedra\Routing\Route $route of where the group is based on
     * @return \Exedra\Routing\Group
     */
    public function createGroup(array $routes = array(), Route $route = null)
    {
        return new Group($this, $route, $routes);
    }
}
