<?php

namespace Sigil\Reflections;

class RouteCollection extends \ArrayIterator
{
    /**
     * @param callable $callback
     * @return array
     */
    public function map(callable $callback) : array
    {
        $result = [];

        foreach ($this as $route) {
            $result[] = $callback($route);
        }

        return $result;
    }

    /**
     * @param callable<RouteReflection> $callback
     * @return RouteCollection
     */
    public function filter(callable $callback)
    {
        $routes = new RouteCollection();

        foreach ($this as $route) {
            if ($callback($route))
                $routes->append($route);
        }

        return $routes;
    }

    public function findByTag($tag)
    {
        return $this->filter(function(RouteReflection $route) use ($tag) {
            return $route->getTag() == $tag;
        });
    }

    public function findByMethod($method)
    {
        return $this->filter(function(RouteReflection $route) use ($method) {
            return $route->getMethod() == $method;
        });
    }

    public function findByUri($uri)
    {
        return $this->filter(function(RouteReflection $route) use ($uri) {
            return str_contains($route->getAbsolutePath(), $uri);
        });
    }

    public function findByControllerMethod($method)
    {
        return $this->filter(function(RouteReflection $route) use ($method) {
            return $route->getControllerMethod() && $route->getControllerMethod() == $method;
        });
    }

    public function findByController($controller)
    {
        return $this->filter(function(RouteReflection $route) use ($controller) {
            return $route->getController() && str_contains($route->getController(), $controller);
        });
    }
}
