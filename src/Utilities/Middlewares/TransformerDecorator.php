<?php

namespace Sigil\Utilities\Middlewares;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use League\Fractal\TransformerAbstract;
use Sigil\Context;
use Sigil\Utilities\Attributes\Model;
use Sigil\Utilities\Attributes\Transformer;

class TransformerDecorator
{
    public function handle(Request $request, $next, Context $context)
    {
        $contents = $next($request);

        $transformer = $context->getState(Transformer::class);

        if (is_object($contents) && $transformer && ($contents instanceof \Illuminate\Database\Eloquent\Model || $contents instanceof Collection)) {
            /** @var TransformerAbstract $transformer */
            $transformer = new $transformer;

            if ($contents instanceof \Illuminate\Database\Eloquent\Model) {
                return fractal()
                    ->item($contents)
                    ->transformWith($transformer)
                    ->toArray();
            }

            if ($contents instanceof Collection) {
                return fractal()
                    ->collection($contents)
                    ->transformWith($transformer)
                    ->toArray();
            }
        }

        return $contents;
    }
}
