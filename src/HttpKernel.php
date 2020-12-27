<?php

namespace Sigil;

use Illuminate\Foundation\Http\Kernel;
use Illuminate\Support\Facades\Facade;

abstract class HttpKernel extends Kernel
{
    abstract public function getSigilSetup() : KernelSetup;

    protected function sendRequestThroughRouter($request)
    {
        $this->app->instance('request', $request);

        Facade::clearResolvedInstance('request');

        $this->bootstrap();

        (new KernelBoot($this->getSigilSetup()))->dispatch($this->app);
    }
}
