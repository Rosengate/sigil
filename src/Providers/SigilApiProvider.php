<?php

namespace Sigil\Providers;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use Sigil\Commands\RouteListCommand;
use Sigil\Sigil;
use Sigil\SigilKernel;

class SigilApiProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../app/Http/Controllers/ApisController.php' => app_path('Http/Controllers/ApisController.php'),
                // middlewares
                __DIR__ . '/../../app/Middlewares/CorsMiddleware.php' => app_path('Middlewares/CorsMiddleware.php'),
                __DIR__ . '/../../app/Middlewares/ExceptionHandlingMiddleware.php' => app_path('Middlewares/ExceptionHandlingMiddleware.php'),
                __DIR__ . '/../../app/Middlewares/RouteModelMiddleware.php' => app_path('Middlewares/RouteModelMiddleware.php'),
                __DIR__ . '/../../app/Middlewares/RulesMiddleware.php' => app_path('Middlewares/RulesMiddleware.php'),
                __DIR__ . '/../../app/Middlewares/TransformerDecorator.php' => app_path('Middlewares/TransformerDecorator.php'),
                // models
                __DIR__ . '/../../app/Models/BaseModel.php' => app_path('Models/BaseModel.php'),
                // transformers
                __DIR__ . '/../../app/Transformers/BaseModelTransformer.php' => app_path('Transformers/BaseModelTransformer.php'),
                __DIR__ . '/../../app/Transformers/Serializer.php' => app_path('Transformers/Serializer.php'),
                // exceptions
                __DIR__ . '/../../app/Exceptions/Routing/RouteNotFoundException.php' => app_path('Exceptions/Routing/RouteNotFoundException.php'),
                // attributes
                __DIR__ . '/../../app/Attributes/Rules.php' => app_path('Attributes/Rules.php'),
                __DIR__ . '/../../app/Attributes/BindModel.php' => app_path('Attributes/BindModel.php'),
                __DIR__ . '/../../app/Attributes/TransformCollection.php' => app_path('Attributes/TransformCollection.php'),
                __DIR__ . '/../../app/Attributes/TransformItem.php' => app_path('Attributes/TransformItem.php'),
            ]);
        }
    }
}
