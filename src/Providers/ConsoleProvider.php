<?php

namespace Sigil\Providers;

use App\Http\Controllers\RootController;
use Illuminate\Support\ServiceProvider;
use Sigil\Commands\RouteListCommand;
use Sigil\KernelBoot;
use Sigil\KernelSetup;

class ConsoleProvider extends ServiceProvider
{
    public function boot()
    {
        (new KernelBoot(new KernelSetup(RootController::class)))->routeSetup($this->app);

        $this->commands([
            RouteListCommand::class
        ]);
    }
}
