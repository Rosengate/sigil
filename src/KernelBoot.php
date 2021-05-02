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
use Sigil\Utilities\UrlGenerator;

class KernelBoot
{
    /**
     * @var KernelSetup
     */
    private KernelSetup $kernelSetup;

    public function __construct(KernelSetup $kernelSetup)
    {
        $this->kernelSetup = $kernelSetup;
    }

    public function routeSetup(Application $app) : Group
    {
        $factory = new RoutingFactory(function($class) {
            return app($class);
        });

        $factory->addGroupHandler($handler = new Handler($app, [], $this->kernelSetup->getCacheInterface(), [
            'reader' => new AttributesReader(),
            'auto_reload' => $this->kernelSetup->isAutoReload()
        ]));

        $factory->addExecuteHandlers(new ExecuteHandler());

        $map = $handler->resolveGroup($factory, $this->kernelSetup->getRootController());

        $map->addMiddlewares($this->kernelSetup->getMiddlewares());
        $map->addDecorators($this->kernelSetup->getDecorators());

        app()->instance('root_group', $map);

        return $map;
    }

    /**
     * HttpKernel dispatch
     * @param Application $app
     * @throws \Exedra\Exception\RouteNotFoundException
     */
    public function dispatch(Application $app)
    {
        $resolver = new LaravelContainerResolver();

        $map = $this->routeSetup($app);

        $finding = $map->findByRequest($request = ServerRequest::createFromGlobals());

        $callStack = $finding->getCallStack();

        $callable = $callStack->getNextCallable();

        $urlGenerator = new \Exedra\Url\UrlGenerator($map, $request);
        $callHandler = new CallHandler(new Wireman([$resolver], [$resolver]));

        app()->instance(CallStack::class, $callStack);
        app()->instance(CallHandler::class, $callHandler);
        app()->instance(Context::class, $finding);
        app()->instance(UrlFactory::class, new UrlFactory($map, $request));

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
}
