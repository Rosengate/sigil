<?php

namespace Sigil;

use Exedra\Routing\CallStack;
use Exedra\Runtime\Context;
use Exedra\Support\Wireman\Contracts\ParamResolver;
use Exedra\Support\Wireman\Contracts\WiringResolver;
use Exedra\Support\Wireman\Wireman;
use Illuminate\Http\Request;

class LaravelContainerResolver implements WiringResolver, ParamResolver
{
    /**
     * @param $pattern
     * @return bool
     */
    public function canResolveWiring($pattern)
    {
        return app()->has($pattern);
    }

    /**
     * @param $pattern
     * @param Wireman $wireman
     * @return mixed
     */
    public function resolveWiring($pattern, Wireman $wireman)
    {
        return app()->make($pattern);
    }

    /**
     * @param \ReflectionParameter $param
     * @return bool
     */
    public function canResolveParam(\ReflectionParameter $param)
    {
        if ($param->getType())
            return true;

        // middleware
        if (in_array($param->getName(), ['next', 'request']))
            return true;

        return false;
    }

    /**
     * @param \ReflectionParameter $param
     * @return mixed
     */
    public function resolveParam(\ReflectionParameter $param, Wireman $wireman)
    {
        $class = $param->getType();

        $name = $param->getName();

        if ($class && $name != 'next')
            return $wireman->resolve((string) $class);

        if ($param->getName() == 'request')
            return $wireman->resolve(Request::class);

        // next
        return function() use ($wireman) {
            /** @var CallStack $stack */
            $stack = $wireman->resolve(CallStack::class);

            /** @var CallHandler $callHandler */
            $callHandler = $wireman->resolve(CallHandler::class);

            return $callHandler->handle($stack->getNextCallable(), func_get_args());
        };
    }
}
