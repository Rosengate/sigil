<?php

namespace Sigil\Reflections;

use Exedra\Routing\Route;
use Illuminate\Contracts\Routing\Registrar;

class RouteReflection
{
    /**
     * @var Route
     */
    public Route $route;

    protected $controller = null;

    /**
     * @var mixed|string
     */
    protected $method = null;

    public function __construct(Route $route)
    {
        $this->route = $route;

        $handler = $this->route->getProperty('execute');

        if (strpos($handler, 'routeller=') === 0) {
            $handler = str_replace('routeller=', '', $handler);

            list($this->controller, $this->method) = explode('@', $handler);
        }
    }

    /**
     * Get controller class name
     * @return string|null
     */
    public function getController() : string|null
    {
        return $this->controller;
    }

    /**
     * Get controller name
     * @return string|null
     */
    public function getControllerMethod() : string|null
    {
        return $this->method;
    }

    /**
     * Get full path
     * @param array $params
     * @return string
     * @throws \Exedra\Exception\InvalidArgumentException
     */
    public function getAbsolutePath(array $params = [])
    {
        return $this->route->getAbsolutePath($params);
    }

    /**
     * Route has action
     * @return bool
     */
    public function isExecutable()
    {
        return $this->route->hasExecution();
    }

    /**
     * Get tag
     * @return mixed|null
     */
    public function getTag()
    {
        return $this->route->getProperty('tag');
    }

    /**
     * Get http method
     * @return mixed|null
     */
    public function getMethod()
    {
        return $this->route->getProperty('method');
    }

    /**
     * Get collective flags
     */
    public function getFlags()
    {
        $flags = [];

        foreach ($this->route->getFullRoutes() as $route) {
            foreach ($route->getFlags() as $flag)
                $flags[] = $flag;
        }

        return $flags;
    }

    /**
     * Has given flag
     * @param $flag
     * @return bool
     */
    public function hasFlag($flag)
    {
        return in_array($flag, $this->getFlags());
    }

    /**
     * Has given state
     * @param $state
     * @return bool
     */
    public function hasState($state)
    {
        return array_key_exists($state, $this->getStates());
    }

    /**
     * Get all states
     * @return array
     */
    public function getStates()
    {
        $states = [];

        foreach ($this->route->getFullRoutes() as $route) {
            foreach ($route->getStates() as $key => $state)
                $states[$key] = $state;
        }

        return $states;
    }

    /**
     * Get all series
     * @return array
     */
    public function getSerieses()
    {
        $serieses = [];

        foreach ($this->route->getSerieses() as $key => $series) {
            if (!array_key_exists($key, $serieses))
                $serieses[$key] = array();

            foreach ($series as $v)
                $serieses[$key][] = $v;
        }

        return $serieses;
    }

    public function getGroupReflection() : RoutingReflection
    {
        return new RoutingReflection($this->route->getGroup());
    }

    /**
     * Get Route
     * @return Route
     */
    public function getRoute()
    {
        return $this->route;
    }
}
