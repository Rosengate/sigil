<?php

namespace Sigil\Providers;

use App\Http\Controllers\RootController;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use Sigil\Commands\RouteListCommand;
use Sigil\HttpKernel;
use Sigil\Sigil;
use Sigil\SigilSetup;

class SigilProvider extends ServiceProvider
{
    public function boot()
    {
        /** @var HttpKernel $kernel */
        $kernel = $this->app->get(Kernel::class);

        $this->app->instance(Sigil::class, new Sigil($kernel->getSigilSetup(), $this->app));

        $this->commands([
            RouteListCommand::class
        ]);
    }
}
