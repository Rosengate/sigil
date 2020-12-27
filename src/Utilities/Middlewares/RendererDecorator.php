<?php

namespace Sigil\Utilities\Middlewares;

use Illuminate\Http\Request;
use Sigil\Context;
use Sigil\Contracts\Renderer;
use Sigil\Exceptions\Exception;

class RendererDecorator
{
    public function handle(Request $request, $next, Context $context)
    {
        $contents = $next($request);

        foreach ($context->getSeries(\Sigil\Utilities\Attributes\Renderer::class, []) as $renderer) {
            /** @var Renderer $renderer */
            $renderer = app($renderer);

            if (!($renderer instanceof Renderer))
                throw new Exception('Not an instance of ' . Renderer::class);

            if (!$renderer->isRenderable($contents))
                continue;

            return $renderer->render($contents);
        }

        return $contents;
    }
}
