<?php

namespace Sigil;

use Exedra\Exception\Exception;
use Exedra\Routeller\Controller\Controller;
use Exedra\Routeller\Handler;
use Exedra\Routing\Factory;
use Exedra\Routing\Group;
use Exedra\Routing\Route;

class VerboseHandler extends Handler
{
    public function resolveGroup(Factory $factory, $pattern, Route $parentRoute = null)
    {
        /** @var \Sigil\Routing\Group $group */
        $group = $factory->createGroup(array(), $parentRoute);

        if (is_object($pattern)) {
//            $classname = get_class($pattern);

            $controller = $pattern;
        } else {
            // resolve pattern
            if (strpos($pattern, 'routeller_class=') === 0) {
                return $this->resolveGroup($factory, str_replace('routeller_class=', '', $pattern), $parentRoute);
                // pattern is uncertain
            } else if (strpos($pattern, 'routeller=') === 0) {
                list($classname, $method) = explode('@', str_replace('routeller=', '', $pattern));

                $controller = $classname::instance()->{$method}($this->container);

                if (!$this->validateGroup($controller))
                    throw new Exception('Unable to validate the routing group for [' . $classname . '::' . $method . '()]');

                $group->setController($controller);

                return $this->resolveGroup($factory, $controller, $parentRoute);

                // for sub prefix
            } else if (strpos($pattern, 'routeller_call') === 0) {
                list($classname, $method) = explode('@', str_replace('routeller_call=', '', $pattern));

                $classname::instance()->{$method}($group, $this->container);

//                $group->setRoutellerClass($classname);

                return $group;
            }

            $classname = $pattern;

            /** @var Controller $controller */
            $controller = $classname::instance();
        }

        $key = md5(get_class($controller));

        $entries = null;

        if ($this->isAutoReload) {
            $reflection = $this->getClassReflection(get_class($controller));

            $lastModified = filemtime($reflection->getFileName());

            $cache = $this->cache->get($key);

            if ($cache) {
                if ($cache['last_modified'] != $lastModified) {
                    $this->cache->clear($key);
                } else {
                    $entries = $cache['entries'];

                    // check for deferred routing cache
                    foreach ($entries as $entry) {
                        if (!isset($entry['route']))
                            continue;

                        if (isset($entry['route']['properties']) &&
                            isset($entry['route']['properties']['subroutes']) &&
                            is_string($entry['route']['properties']['subroutes']) && strpos($entry['route']['properties']['subroutes'], 'routeller_class=') === 0) {

                            @list($subroutesClass) = explode('@', str_replace('routeller_class=', '', $entry['route']['properties']['subroutes']));
                            $subroutesKey = md5($subroutesClass);
                            $subroutesCache = $this->cache->get($subroutesKey);

                            $ref = $this->getClassReflection($subroutesClass);

                            // check cache for subroutes
                            if (!$subroutesCache || ($subroutesCache && $subroutesCache['last_modified'] != filemtime($ref->getFileName()))) {
                                $entries = null;
                                $this->cache->clear($key);
                                $this->cache->clear($subroutesKey);
                                break;
                            }
                        }
                    }
                }
            }
        } else {
            $cache = $this->cache->get($key);

            if ($cache)
                $entries = $cache['entries'];
        }

        // cache entries handling
        if ($entries) {
            foreach ($entries as $entry) {
                if (isset($entry['middleware'])) {
                    $group->addMiddleware(function () use ($controller, $entry) {
                        return call_user_func_array(array($controller, $entry['middleware']['handle']), func_get_args());
                    }, $entry['middleware']['properties']);
                } else if (isset($entry['route'])) {
//                    $properties = $entry['route']['properties'];

                    $merges = $this->resolveProperties($entry['route']['properties']);

                    $group->addRoute($route = $factory->createRoute($group, isset($merges['name']) ? $merges['name'] : $entry['route']['name'], $merges));

                    if (isset($entry['route']['route_call'])) {
                        list($classname, $methodName) = explode('@', $entry['route']['route_call']);

                        $classname::instance()->{$methodName}($route, $this->container);
                    }
                } else if (isset($entry['setup'])) {
                    $controller::instance()->{$entry['setup']['method']}($group, $this->container);
                }
            }

            return $group;
        }

        if (!$this->isAutoReload) {
            $reflection = $this->getClassReflection(get_class($controller));
        }

        if (isset($reflection) && !$reflection->isSubclassOf(Controller::class))
            throw new Exception('[' . $classname . '] must be a type of [' . Controller::class . ']');

        $reader = $this->createReader();

        if ($parentRoute && !isset($this->read[$reflection->getName()])) {
            $parentRoute->setProperties($this->readReflectionClassProperties($reflection, $reader));
        }

        $entries = array();

        // loop all the refClass's methods
        foreach ($reflection->getMethods() as $reflectionMethod) {
            $methodName = $reflectionMethod->getName();

            if (strpos($methodName, 'middleware') === 0) {
                $properties = $reader->readProperties($reflectionMethod);

                $entries[] = array(
                    'middleware' => array(
                        'properties' => $properties,
                        'handle' => $reflectionMethod->getName()
                    )
                );

                if (isset($properties['inject'])) {
                    $properties['dependencies'] = $properties['inject'];
                    unset($properties['inject']);
                }

                if (isset($properties['dependencies']) && is_string($properties['dependencies']))
                    $properties['dependencies'] = array_map('trim', explode(',', trim($properties['dependencies'], ' []')));

                $group->addMiddleware($reflectionMethod->getClosure($controller), $properties);

                continue;
            }

            if (strpos($methodName, 'decorate') === 0) {
                $entries = array(
                    'decorator' => array(
                        'properties' => [],
                        'handle' => $reflectionMethod->getName()
                    )
                );

                $group->addDecorator($reflectionMethod->getClosure($controller));

                continue;
            }

            if (strpos(strtolower($methodName), 'setup') === 0) {
                $controller->{$methodName}($group, $this->container);

                $entries[] = array(
                    'setup' => array(
                        'method' => $methodName
                    )
                );

                continue;
            }

            $type = null;

            $method = null;

            if ($routeName = $this->parseExecuteMethod($methodName)) {
                $type = 'execute';
            } else if ($routeName = $this->parseGroupMethod($methodName)) {
                $type = 'subroutes';
            } else if ($result = $this->parseRestfulMethod($methodName)) {
                $type = 'execute';

                @list($routeName, $method) = $result;
            } else if ($routeName = $this->parseSubMethod($methodName)) {
                $type = 'subroutes_call';
            } else if ($routeName = $this->parseRouteMethod($methodName)) {
                $type = 'route_call';
            } else {
                continue;
            }

            $properties = $reader->readProperties($reflectionMethod);

            // read from route properties from the refClass itself
            $subrouteClass = null;

            if ($type == 'subroutes') {
                $cname = $controller->{$methodName}($this->container);

                if ($this->validateGroup($cname)) {
                    $controllerRef = $this->getClassReflection($controller->{$methodName}($this->container));

                    if (!$controllerRef->isSubclassOf(Controller::class))
                        throw new Exception('[' . $cname . '] must be a type of [' . Controller::class . ']');

                    // read controller route properties
                    $properties = $this->propertiesDeferringMerge($this->readReflectionClassProperties($controllerRef, $reader), $properties);

                    // to prevent route setProperties re-run above.
                    $this->read[$controllerRef->getName()] = true;

                    $subrouteClass = $cname;
                }
            }

            if ($method && !isset($properties['method']))
                $properties['method'] = $method;

            if (count($properties) == 0)
                continue;

            if ($type == 'execute') { // if it is, save the closure.
                $properties['execute'] = 'routeller=' . $classname . '@' . $reflectionMethod->getName();
            } else if ($type == 'subroutes') {
                $properties['subroutes'] = $controller->{$methodName}($this->container);
            }

            if (isset($properties['name']))
                $properties['name'] = (string)$properties['name'];

            if (isset($properties['inject']) && is_string($properties['inject']))
                $properties['inject'] = array_map('trim', explode(',', trim($properties['inject'], ' []')));

            $merges = $this->resolveProperties($properties);

            $group->addRoute($route = $factory->createRoute($group, $routeName = (isset($merges['name']) ? $merges['name'] : $routeName), $merges));

            // caching preparation
            $entry = array(
                'route' => array(
                    'name' => $routeName,
                    'properties' => $properties
                )
            );

            if ($type == 'subroutes_call') {
                $app = $this->container;

                $route->group(function (Group $group) use ($controller, $methodName, $app) {
                    $controller->{$methodName}($group);
                });

                $entry['route']['properties']['subroutes'] = 'routeller_call=' . $classname . '@' . $methodName;
            } else if (isset($properties['subroutes'])) {
                if ($subrouteClass)
                    $entry['route']['properties']['subroutes'] = 'routeller_class=' . $subrouteClass;
                else
                    $entry['route']['properties']['subroutes'] = 'routeller=' . $classname . '@' . $methodName;

            } else if ($type == 'route_call') {
                $controller->{$methodName}($route, $this->container);
                $entry['route']['route_call'] = $classname . '@' . $methodName;
            }

            $entries[] = $entry;
        }

        $this->cache->set($key, $entries, isset($lastModified) ? $lastModified : filemtime($reflection->getFileName()));

        $group->setController(is_object($controller) ? get_class($controller) : $controller);

        return $group;
    }
}
