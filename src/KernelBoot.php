<?php

namespace Sigil;

use App\Http\Controllers\RootController;
use Exedra\Http\ServerRequest;
use Exedra\Routeller\AttributesReader;
use Exedra\Routeller\ExecuteHandler;
use Exedra\Routeller\Handler;
use Exedra\Routing\CallStack;
use Exedra\Routing\Group;
use Exedra\Support\Wireman\Wireman;
use Exedra\Url\UrlFactory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Response;

class KernelBoot
{
    protected $rootController;

    public function __construct($rootController)
    {
        $this->rootController = $rootController;
    }

    public function routeSetup(Application $app) : Group
    {
        $factory = new RoutingFactory(function($class) {
            return app($class);
        });

        $factory->addGroupHandler($handler = new Handler($app, [], null, [
            'reader' => new AttributesReader()
        ]));

        $factory->addExecuteHandlers(new ExecuteHandler());

        return $handler->resolveGroup($factory, $this->rootController);
    }

    public function dispatch(Application $app)
    {
        $resolver = new LaravelContainerResolver();

        $map = $this->routeSetup($app);

        $finding = $map->findByRequest($request = ServerRequest::createFromGlobals());

        $callStack = $finding->getCallStack();

        $callable = $callStack->getNextCallable();

//        $callHandler = new CallHandler();
        $callHandler = new CallHandler(new Wireman([$resolver], [$resolver]));

        app()->instance(CallStack::class, $callStack);
        app()->instance(CallHandler::class, $callHandler);
        app()->instance(Context::class, $finding);
        app()->instance(UrlFactory::class, new UrlFactory($map, $request));

        /** @var Response $response */
        $response =  $callHandler->handle($callable, [$request, $callStack->getNextCaller()]);

        $response->send();
        exit;
    }
}
