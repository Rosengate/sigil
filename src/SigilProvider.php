<?php

namespace Sigil;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use Sigil\Commands\RouteListCommand;

class SigilProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/sigil.php' => config_path('sigil.php'),
            __DIR__ . '/../views/welcome-sigil.blade.php' => resource_path('views/welcome-sigil.php'),
            __DIR__ . '/../app/Http/Controllers/RootController.php' => app_path('Http/Controllers/RootController.php'),
            __DIR__ . '/../app/Http/Controllers/WebController.php' => app_path('Http/Controllers/WebController.php'),
        ]);

        /** @var SigilKernel $kernel */
        $kernel = $this->app->get(Kernel::class);

        $setup = $kernel->getSigilSetup();

        if ($setup) {
            $this->app->instance(Sigil::class, new Sigil($setup, $this->app));

            $this->commands([
                RouteListCommand::class
            ]);
        }
    }
}
