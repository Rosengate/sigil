<?php

namespace Sigil\Reflections;

use Exedra\Exception\RouteNotFoundException;
use Exedra\Routing\Route;
use Psr\Http\Message\ServerRequestInterface;
use Sigil\Routing\Group;

class RoutingReflection
{
    /**
     * @var Group
     */
    private Group $group;

    public function __construct(Group $group)
    {
        $this->group = $group;
    }

    /**
     * @return Group
     */
    public function getRouter()
    {
        return $this->group;
    }

    /**
     * Get controller for this group
     * @return mixed
     */
    public function getController()
    {
        return $this->group->getController();
    }

    /**
     * @param ServerRequestInterface $request
     * @return RouteReflection
     * @throws RouteNotFoundException
     */
    public function findByRequest(ServerRequestInterface $request): RouteReflection
    {
        return new RouteReflection($this->group->findByRequest($request)->getRoute());
    }

    /**
     * Get all routes under this group
     * @param false $deep
     * @return RouteCollection|RouteReflection[]
     */
    public function getRoutes($deep = false)
    {
        $routes = new RouteCollection();

        $this->group->each(function(Route $route) use (&$routes) {
            $routes->append(new RouteReflection($route));
        }, $deep);

        return $routes;
    }
}
