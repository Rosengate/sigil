<?php

namespace Sigil\Utilities\Middlewares;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\AbstractPaginator;
use League\Fractal\Resource\ResourceAbstract;
use League\Fractal\TransformerAbstract;
use Sigil\Context;
use Sigil\Utilities\Attributes\Model;
use Sigil\Utilities\Attributes\Transformer;
use Sigil\Utilities\TransformerSerializer;

/**
 * @deprecated
 */
class TransformerDecorator
{
    public function handle(Request $request, $next, Context $context)
    {
        $contents = $next($request);

        $transformer = $context->getState(Transformer::class);

        if (is_object($contents) && $transformer && ($contents instanceof \Illuminate\Database\Eloquent\Model || $contents instanceof Collection || $contents instanceof AbstractPaginator)) {
            /** @var TransformerAbstract $transformer */
            $transformer = new $transformer;

            $response = [];

            $serializer = new TransformerSerializer();

            $fractal = fractal();

            if ($includes = $request->get('includes'))
                $fractal->parseIncludes(is_array($includes) ? $includes : explode(',', $includes));

            if ($contents instanceof \Illuminate\Database\Eloquent\Model) {
                $response['data'] = $fractal
                    ->serializeWith($serializer)
                    ->item($contents)
                    ->transformWith($transformer)
                    ->toArray();
            }

            if ($contents instanceof Collection) {
                $response['data'] = $fractal
                    ->serializeWith($serializer)
                    ->collection($contents)
                    ->transformWith($transformer)
                    ->toArray();
            }

            if ($contents instanceof AbstractPaginator) {
                $response = [];

                $contents->appends($_GET);

                $response['data'] = $fractal
                    ->serializeWith($serializer)
                    ->collection($contents)
                    ->transformWith($transformer)
                    ->toArray();

                $serialized = $contents->toArray();

                unset($serialized['data']);

                $response['pagination'] = $serialized;
            }

            if ($response instanceof ResourceAbstract) {
                $arr['data'] = $fractal
                    ->data($contents)
                    ->transformWith($transformer)
                    ->toArray();
            }

            return $response;
        }

        return $contents;
    }
}
