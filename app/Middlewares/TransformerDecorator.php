<?php

namespace App\Middlewares;

use App\Attributes\TransformCollection;
use App\Attributes\TransformItem;
use App\Models\BaseModel;
use App\Transformers\BaseModelTransformer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\AbstractPaginator;
use League\Fractal\Resource\ResourceAbstract;
use League\Fractal\TransformerAbstract;
use Sigil\Context;
use Sigil\Utilities\Attributes\Transformer;

class TransformerDecorator
{
    public function handle(Request $request, $next, Context $context)
    {
        $contents = $next($request);

//        $transformer = $context->getState(Transformer::class, $context);
//        $transformer = $context->getState(TransformItem::class);

        $transformItem = $context->getState(TransformItem::class);
        $transformCollection = $context->getState(TransformCollection::class);

//        if ($contents instanceof BaseModel) {
//            $class = $contents::class;
//            $class = str_replace('Model', 'Transformer', $class);
//
//            if (class_exists($class))
//                $transformer = $class;
//        }
//
//        if ($contents instanceof Collection) {
//            if (($first = $contents->first()) && $first instanceof BaseModel) {
//                $class = $first::class;
//                $class = str_replace('Model', 'Transformer', $class);
//
//                if (class_exists($class))
//                    $transformer = $class;
//            }
//        }

        $transformer = BaseModelTransformer::getTransformerIfExist($contents);

        if ($transformer || $transformItem || $transformCollection) {
            /** @var TransformerAbstract $transformer */
            $transformer = $transformItem ? new $transformItem : ($transformCollection ? new $transformCollection : new $transformer);

            $response = [];

            $serializer = new \App\Transformers\Serializer();

            $fractal = fractal();

            if ($includes = $request->get('includes'))
                $fractal->parseIncludes(is_array($includes) ? $includes : explode(',', $includes));

            if (is_array($contents)) {
                if ($transformItem) {
                    $response['data'] = $fractal
                        ->serializeWith($serializer)
                        ->item($contents)
                        ->transformWith($transformer)
                        ->toArray();
                } if ($transformCollection) {
                    $response['data'] = $fractal
                        ->serializeWith($serializer)
                        ->collection($contents)
                        ->transformWith($transformer)
                        ->toArray();
                }
            }

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

            return $response;
        } else if ($contents instanceof Collection) {
            return ['data' => $contents->toArray()];
        } else if ($contents instanceof BaseModel) {
            return ['data' => $contents->toArray()];
        } else if ($contents instanceof Model) {
            return ['data' => $contents->toArray()];
        } else if (is_array($contents)) {
            return ['data' => $contents];
        }

        return $contents;
    }
}
