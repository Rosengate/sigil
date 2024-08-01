<?php

namespace Sigil;

use Illuminate\Foundation\Configuration\ApplicationBuilder;

class SigilApplicationBuilder extends ApplicationBuilder
{
    public function create()
    {
        $app = parent::create(); // TODO: Change the autogenerated stub

        $app->singleton(\Illuminate\Contracts\Http\Kernel::class, SigilKernel::class);

        return $app;
    }
}
