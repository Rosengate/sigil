<?php

namespace Sigil;

use Illuminate\Foundation\Http\Kernel;
use Illuminate\Support\Facades\Facade;

abstract class HttpKernel extends Kernel
{
    abstract public function getRootController() : string;

    protected function sendRequestThroughRouter($request)
    {
        $this->app->instance('request', $request);

        Facade::clearResolvedInstance('request');

        $this->bootstrap();

        (new KernelBoot($this->getRootController()))->dispatch($this->app);
    }
}
