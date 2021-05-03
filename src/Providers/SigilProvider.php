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
        $this->publishes([
            __DIR__ . '/../../config/sigil.php' => config_path('sigil.php')
        ]);

        /** @var HttpKernel $kernel */
        $kernel = $this->app->get(Kernel::class);

        $setup = $kernel->getSigilSetup();

        if ($setup)
            $this->app->instance(Sigil::class, new Sigil($setup, $this->app));

        $this->commands([
            RouteListCommand::class
        ]);
    }
}
