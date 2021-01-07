<?php

namespace Sigil;

use Exedra\Exception\RouteNotFoundException;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Routing\Pipeline;
use Illuminate\Support\Facades\Facade;

abstract class HttpKernel extends Kernel
{
    abstract public function getSigilSetup() : KernelSetup;

    protected function sendRequestThroughRouter($request)
    {
        $this->app->instance('request', $request);

        Facade::clearResolvedInstance('request');

        $this->bootstrap();

        try {
            (new KernelBoot($this->getSigilSetup()))->dispatch($this->app);
        } catch (RouteNotFoundException $e) {

            return (new Pipeline($this->app))
                ->send($request)
                ->through($this->app->shouldSkipMiddleware() ? [] : $this->middleware)
                ->then($this->dispatchToRouter());
        }
    }
}
