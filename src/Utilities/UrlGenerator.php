<?php

namespace Sigil\Utilities;

use Exedra\Exception\NotFoundException;
use Exedra\Exception\RouteNotFoundException;
use Exedra\Url\UrlFactory;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteCollectionInterface;

class UrlGenerator extends \Illuminate\Routing\UrlGenerator
{
    private UrlFactory $urlFactory;

    public function __construct(RouteCollectionInterface $routes,
                                Request $request,
                                $assetUrl,
                                UrlFactory $urlFactory)
    {
        $this->urlFactory = $urlFactory;

        parent::__construct($routes, $request, $assetUrl);
    }

    public function route($name, $parameters = [], $absolute = true)
    {
        try {
            return (string) $this->urlFactory->route($name, $parameters);
        } catch (NotFoundException $e) {
            return parent::route($name, $parameters, $absolute);
        }
    }
}
