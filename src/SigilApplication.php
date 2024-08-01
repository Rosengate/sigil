<?php

namespace Sigil;

use Illuminate\Foundation\Application;

/**
 * Laravel 11 support
 */
class SigilApplication extends Application
{
    public static function configure(?string $basePath = null)
    {
        $basePath = match (true) {
            is_string($basePath) => $basePath,
            default => static::inferBasePath(),
        };

        return (new SigilApplicationBuilder(new static($basePath)))
            ->withKernels()
            ->withEvents()
            ->withCommands()
            ->withProviders();
    }
}
