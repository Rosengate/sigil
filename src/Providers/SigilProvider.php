<?php

namespace Sigil\Providers;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use Sigil\Commands\RouteListCommand;
use Sigil\Sigil;
use Sigil\SigilKernel;

class SigilProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/sigil.php' => config_path('sigil.php'),
                // controllers
                __DIR__ . '/../../views/welcome-sigil.blade.php' => resource_path('views/welcome-sigil.blade.php'),
                __DIR__ . '/../../app/Http/Controllers/RootController.php' => app_path('Http/Controllers/RootController.php'),
                __DIR__ . '/../../app/Http/Controllers/WebController.php' => app_path('Http/Controllers/WebController.php'),
                // models
                __DIR__ . '/../../app/Models/BaseModel.php' => app_path('Models/BaseModel.php'),
                // transformers
                // exceptions
                __DIR__ . '/../../app/Exceptions/Exception.php' => app_path('Exceptions/Exception.php'),
            ]);
        }

        $kernel = $this->app->get(Kernel::class);

        $setup = $kernel instanceof SigilKernel ? $kernel->getSigilSetup() : null;

        if ($setup) {
            $this->app->instance(Sigil::class, new Sigil($setup, $this->app));

            $this->commands([
                RouteListCommand::class
            ]);
        }
    }
}
