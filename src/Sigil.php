<?php

namespace Sigil;

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
use Illuminate\Routing\Redirector;
use Sigil\Reflections\RoutingReflection;
use Sigil\Utilities\UrlGenerator;

class Sigil
{
    /**
     * @var SigilSetup
     */
    private SigilSetup $setup;

    /**
     * @var Application
     */
    private Application $app;

    /**
     * @var Group
     */
    private Group $map;

    public function __construct(SigilSetup $setup, Application $app)
    {
        $this->setup = $setup;
        $this->app = $app;
        $this->map = $this->routeSetup($app);
    }

    /**
     * @return Group
     */
    public function getRouting()
    {
        return $this->map;
    }

    protected function routeSetup(Application $app) : Group
    {
        $factory = new RoutingFactory(function($class) {
            return app($class);
        });

        $handler = new VerboseHandler($app, [], $this->setup->getCacheInterface(), [
            'reader' => new AttributesReader(),
            'auto_reload' => $this->setup->isAutoReload()
        ]);

        $factory->addGroupHandler($handler);

        $factory->addExecuteHandlers(new ExecuteHandler());

        $map = $handler->resolveGroup($factory, $this->setup->getRootController());

        $map->addMiddlewares($this->setup->getMiddlewares());
        $map->addDecorators($this->setup->getDecorators());

        return $map;
    }

    /**
     * HttpKernel dispatch
     * @throws \Exedra\Exception\RouteNotFoundException
     */
    public function dispatch()
    {
        $resolver = new LaravelContainerResolver();

        $finding = $this->map->findByRequest($request = ServerRequest::createFromGlobals());

        $callStack = $finding->getCallStack();

        $callable = $callStack->getNextCallable();

        $callHandler = new CallHandler(new Wireman([$resolver], [$resolver]));

        app()->instance(CallStack::class, $callStack);
        app()->instance(CallHandler::class, $callHandler);
        app()->instance(Context::class, $finding);
        app()->instance(UrlFactory::class, new UrlFactory($this->map, $request));

        app()->bind('url', function(\Illuminate\Foundation\Application $app) {
            return new UrlGenerator(
                $app['router']->getRoutes(),
                $app->rebinding('request', function($app, $request) {
                    $app['url']->setRequest($request);
                }),
                $app['config']['app.asset_url'],
                app(UrlFactory::class)
            );
        });

        /** @var Response $response */
        $response =  $callHandler->handle($callable, [$request, $callStack->getNextCaller()]);

        $response->send();
        exit;
    }

    public function getRoutingReflection()
    {
        return new RoutingReflection($this->map);
    }
}
