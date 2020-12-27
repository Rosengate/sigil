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

        return $handler->resolveGroup($factory, $this->kernelSetup->getRootController());
    }

    public function dispatch(Application $app)
    {
        $resolver = new LaravelContainerResolver();

        $map = $this->routeSetup($app);

        $map->addMiddlewares($this->kernelSetup->getMiddlewares());
        $map->addDecorators($this->kernelSetup->getDecorators());

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
